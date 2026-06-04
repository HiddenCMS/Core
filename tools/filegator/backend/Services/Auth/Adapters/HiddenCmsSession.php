<?php

namespace Filegator\Services\Auth\Adapters;

use Filegator\Services\Auth\AuthInterface;
use Filegator\Services\Auth\User;
use Filegator\Services\Auth\UsersCollection;
use Filegator\Services\Service;
use Filegator\Services\Session\SessionStorageInterface as Session;
use PDO;

class HiddenCmsSession implements Service, AuthInterface
{
    const SESSION_KEY = 'hiddencms_auth';
    const SESSION_HASH = 'hiddencms_auth_hash';

    protected $session;
    protected $config = [];
    protected $pdo;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function init(array $config = [])
    {
        $defaults = [
            'db_file' => dirname(__DIR__, 5).'/config/db.php',
            'cookie_name' => 'session',
            'admin_only' => true,
            'private_repos' => false,
            'permissions_admin' => ['read', 'write', 'upload', 'download', 'batchdownload', 'zip', 'chmod'],
            'permissions_user' => ['read', 'upload', 'download', 'batchdownload'],
            'admin_role' => 'admin',
        ];

        $this->config = array_merge($defaults, $config);
    }

    public function user(): ?User
    {
        $stored = $this->session->get(self::SESSION_KEY, null);
        $hash = $this->session->get(self::SESSION_HASH, null);

        if ($stored && $hash) {
            $fresh = $this->find($stored->getUsername());

            if ($fresh && $this->buildUserHash($fresh) === $hash) {
                return $fresh;
            }
        }

        $hidden_user = $this->resolveHiddenCmsUserFromRequest();

        if (! $hidden_user) {
            return null;
        }

        $user = $this->mapArrayToUserObject($hidden_user);
        $this->store($user);

        return $user;
    }

    public function authenticate($username, $password): bool
    {
        $row = $this->queryOne(
            'SELECT id, username, email, password, admin, deleted FROM user WHERE deleted = 0 AND (username = :username OR email = :username) LIMIT 1',
            [':username' => (string)$username]
        );

        if (! $row) {
            return false;
        }

        if (! password_verify((string)$password, (string)$row['password'])) {
            return false;
        }

        $user = $this->mapArrayToUserObject($row);
        $this->store($user);
        $this->session->migrate(true);

        return true;
    }

    public function forget()
    {
        return $this->session->invalidate();
    }

    public function find($username): ?User
    {
        $row = $this->queryOne(
            'SELECT id, username, email, admin, deleted FROM user WHERE deleted = 0 AND username = :username LIMIT 1',
            [':username' => (string)$username]
        );

        if (! $row) {
            return null;
        }

        return $this->mapArrayToUserObject($row);
    }

    public function store(User $user)
    {
        $this->session->set(self::SESSION_KEY, $user);
        $this->session->set(self::SESSION_HASH, $this->buildUserHash($user));
    }

    public function update($username, User $user, $password = ''): User
    {
        throw new \Exception('User update is managed by HiddenCMS.');
    }

    public function add(User $user, $password): User
    {
        throw new \Exception('User creation is managed by HiddenCMS.');
    }

    public function delete(User $user)
    {
        throw new \Exception('User deletion is managed by HiddenCMS.');
    }

    public function getGuest(): User
    {
        $guest = new User();
        $guest->setUsername('guest');
        $guest->setName('Guest');
        $guest->setRole('guest');
        $guest->setHomedir('/');
        $guest->setPermissions([]);

        return $guest;
    }

    public function allUsers(): UsersCollection
    {
        $users = new UsersCollection();
        $rows = $this->queryAll('SELECT id, username, email, admin, deleted FROM user WHERE deleted = 0 ORDER BY username ASC');

        foreach ($rows as $row) {
            $users->addUser($this->mapArrayToUserObject($row));
        }

        return $users;
    }

    protected function resolveHiddenCmsUserFromRequest(): ?array
    {
        $session_id = $this->resolveSessionIdFromRequest();

        if ($session_id === null) {
            return null;
        }

        return $this->queryOne(
            'SELECT u.id, u.username, u.email, u.admin, u.deleted
               FROM session s
          LEFT JOIN user u ON u.id = s.user_id
              WHERE s.id = :session_id
              LIMIT 1',
            [':session_id' => $session_id]
        );
    }

    protected function resolveSessionIdFromRequest(): ?string
    {
        if (!empty($_GET['hb_sid'])) {
            $sid = (string) $_GET['hb_sid'];

            if (preg_match('/^[a-z0-9]{32}$/i', $sid)) {
                return $sid;
            }
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            $query = parse_url((string) $_SERVER['HTTP_REFERER'], PHP_URL_QUERY);

            if (is_string($query) && $query !== '') {
                parse_str($query, $params);

                if (!empty($params['hb_sid'])) {
                    $sid = (string) $params['hb_sid'];

                    if (preg_match('/^[a-z0-9]{32}$/i', $sid)) {
                        return $sid;
                    }
                }
            }
        }

        $cookie_names = $this->resolveCookieNames();

        foreach ($cookie_names as $cookie_name) {
            if (!empty($_COOKIE[$cookie_name])) {
                $sid = (string) $_COOKIE[$cookie_name];

                if (preg_match('/^[a-z0-9]{32}$/i', $sid)) {
                    return $sid;
                }
            }
        }

        return null;
    }

    protected function resolveCookieNames(): array
    {
        $cookie_name = (string)$this->queryValue(
            "SELECT value FROM settings WHERE site = '' AND lang = '' AND name = 'cookie_name' LIMIT 1",
            []
        );

        $cookie_name = trim($cookie_name) !== '' ? trim($cookie_name) : (string)$this->config['cookie_name'];
        $names = [$cookie_name, $cookie_name.'_https'];

        return array_values(array_unique(array_filter($names)));
    }

    protected function mapArrayToUserObject(array $row): User
    {
        $is_admin = !empty($row['admin']) && (string)$row['admin'] !== '0';

        if (! empty($this->config['admin_only']) && !$is_admin) {
            return $this->getGuest();
        }

        $user = new User();
        $user->setUsername((string)($row['username'] ?? ''));
        $user->setName((string)($row['username'] ?? 'User'));
        $user->setRole($is_admin ? (string)$this->config['admin_role'] : 'user');
        $user->setPermissions($is_admin ? (array)$this->config['permissions_admin'] : (array)$this->config['permissions_user']);
        $user->setHomedir('/');

        if (!$is_admin && !empty($this->config['private_repos'])) {
            $user->setHomedir('/'.(string)($row['username'] ?? 'user'));
        }

        return $user;
    }

    protected function buildUserHash(User $user): string
    {
        return sha1($user->getUsername().'|'.$user->getRole().'|'.$user->getPermissions(true).'|'.$user->getHomeDir());
    }

    protected function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        if (! file_exists($this->config['db_file'])) {
            throw new \Exception('HiddenCMS db config file not found.');
        }

        $db = [];
        include $this->config['db_file'];

        if (empty($db[0]) || !is_array($db[0])) {
            throw new \Exception('Invalid HiddenCMS db config.');
        }

        $cfg = $db[0];
        $dsn = 'mysql:host='.$cfg['hostname'].';dbname='.$cfg['database'].';charset=utf8mb4';

        $this->pdo = new PDO($dsn, (string)$cfg['username'], (string)$cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return $this->pdo;
    }

    protected function queryOne(string $sql, array $params): ?array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row && is_array($row) ? $row : null;
    }

    protected function queryAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        return is_array($rows) ? $rows : [];
    }

    protected function queryValue(string $sql, array $params)
    {
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();

        return $value === false ? null : $value;
    }
}
