<?php
namespace HB\Modules\Settings\Models;

use HB\HiddenCMS\Loadables\Model;

class Smtp extends Model
{
    public function values()
    {
        $email = [];
        if (is_file('config/email.php')) include 'config/email.php';
        $legacy = $email['smtp'] ?? [];
        $defaults = ['enabled' => !empty($legacy['host']) ? '1' : '0', 'host' => $legacy['host'] ?? '',
            'port' => !empty($legacy['host']) ? ($legacy['port'] ?? 25) : 587, 'secure' => !empty($legacy['host']) ? ($legacy['secure'] ?? '') : 'tls',
            'username' => $legacy['username'] ?? '', 'from' => '', 'name' => ''];
        foreach ($defaults as $key => &$value) $value = $this->config->{'smtp_'.$key} ?? $value;
        return $defaults;
    }

    public function transport()
    {
        $values = $this->values();
        if (!(int)$values['enabled']) return ['host' => ''];
        $email = [];
        if (is_file('config/email.php')) include 'config/email.php';
        $password = $email['smtp']['password'] ?? '';
        if (isset($this->config->smtp_password)) $password = $this->decrypt($this->config->smtp_password);
        return ['host' => $values['host'], 'port' => (int)$values['port'], 'secure' => $values['secure'],
            'username' => utf8_html_entity_decode($values['username']), 'password' => $password];
    }

    public function save($data)
    {
        if (!in_array($data['smtp_enabled'] ?? NULL, ['0', '1'], TRUE) ||
            !in_array($data['smtp_secure'] ?? NULL, ['', 'tls', 'ssl'], TRUE) ||
            filter_var($data['smtp_port'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === FALSE) throw new \InvalidArgumentException((string)$this->lang('Invalid SMTP settings.'));
        $host = $data['smtp_host'] ?? '';
        if (($data['smtp_enabled'] === '1' && $host === '') || ($host !== '' && !filter_var($host, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME))) throw new \InvalidArgumentException((string)$this->lang('Invalid SMTP server.'));
        if (!empty($data['smtp_from']) && !filter_var($data['smtp_from'], FILTER_VALIDATE_EMAIL)) throw new \InvalidArgumentException((string)$this->lang('Invalid sender.'));
        $secret = NULL;
        if (($data['smtp_clear_password'] ?? '0') === '1') $secret = '';
        elseif ($data['smtp_password'] !== '') {
            $password = utf8_html_entity_decode($data['smtp_password']);
            $iv = random_bytes(12); $tag = '';
            $encrypted = openssl_encrypt($password, 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, $iv, $tag);
            if ($encrypted === FALSE) throw new \RuntimeException((string)$this->lang('Unable to protect the SMTP password.'));
            $secret = base64_encode($iv.$tag.$encrypted);
        }
        foreach ($this->values() as $key => $value) $this->config('smtp_'.$key, $data['smtp_'.$key]);
        if ($secret !== NULL) $this->config('smtp_password', $secret);
    }

    private function key()
    {
        $crypt = [];
        include 'config/crypt.php';
        if (empty($crypt['key'])) throw new \RuntimeException((string)$this->lang('SMTP encryption key unavailable.'));
        return hash('sha256', $crypt['key'], TRUE);
    }

    private function decrypt($value)
    {
        if ($value === '') return '';
        $raw = base64_decode($value, TRUE);
        if ($raw === FALSE || strlen($raw) < 28) throw new \RuntimeException((string)$this->lang('Unable to read the SMTP password.'));
        $password = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $this->key(), OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
        if ($password === FALSE) throw new \RuntimeException((string)$this->lang('Unable to read the SMTP password.'));
        return $password;
    }
}
