<?php
namespace HB\Modules\Statistics\Models;
use HB\HiddenCMS\Loadables\Model;
class Traffic extends Model
{
    public function ready() { return !array_diff(['statistics_daily', 'statistics_seen'], $this->db->tables()); }

    public function path($path)
    {
        if (!is_string($path) || strlen($path) > 255 || $path === '' || $path[0] !== '/' || substr($path, 0, 2) === '//' || preg_match('/[?#\\\\\x00-\x20\x7f-\xff]/', $path)) return NULL;
        $decoded = rawurldecode($path);
        if (preg_match('~(?:^|/)(?:admin|ajax|install|vendor|config|tools|user|files)(?:/|$)~i', $decoded) || preg_match('/[?#\\\\\x00-\x20\x7f]/', $decoded)) return NULL;
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function record($path, $visitor, $day = NULL, $transaction = TRUE)
    {
        $path = $this->path($path);
        if ($path === NULL || !is_string($visitor) || !preg_match('/^[a-f0-9]{64}$/D', $visitor)) return FALSE;
        $day = $day ?? date('Y-m-d');
        if (!is_string($day) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $day)) return FALSE;
        // Keep only aggregate counts long-term; daily session hashes expire after two days.
        $this->db->execute_checked('DELETE FROM statistics_seen WHERE day < DATE_SUB(CURDATE(), INTERVAL 2 DAY)');
        $this->db->execute_checked('DELETE FROM statistics_daily WHERE day < DATE_SUB(CURDATE(), INTERVAL 13 MONTH)');
        if ($transaction) $this->db->begin_transaction();
        try {
            foreach (['', $path] as $key) {
                $this->db->execute_checked('INSERT IGNORE INTO statistics_seen (day, visitor, path) VALUES ("'.$day.'", "'.$visitor.'", "'.$this->db->escape_string($key).'")');
                $fresh = (int)$this->db->query('SELECT ROW_COUNT() AS total')->row() > 0;
                $this->db->execute_checked('INSERT INTO statistics_daily (day, path, views, visits) VALUES ("'.$day.'", "'.$this->db->escape_string($key).'", 1, '.($fresh ? 1 : 0).') ON DUPLICATE KEY UPDATE views = views + 1, visits = visits + '.($fresh ? 1 : 0));
            }
            if ($transaction) $this->db->commit();
        } catch (\Throwable $error) { if ($transaction) $this->db->rollback(); throw $error; }
        return TRUE;
    }

    public function report($days)
    {
        $days = HB()->module('statistics')->model('overview')->period($days);
        if (!$this->ready()) return ['ready' => FALSE, 'views' => 0, 'visits' => 0, 'pages' => [], 'daily' => []];
        $start = (new \DateTimeImmutable('tomorrow'))->modify('-'.$days.' days')->format('Y-m-d');
        $totals = $this->db->select('COALESCE(SUM(views),0) AS views', 'COALESCE(SUM(visits),0) AS visits')->from('statistics_daily')->where('path', '')->where('day >=', $start)->row();
        return ['ready' => TRUE, 'views' => (int)$totals['views'], 'visits' => (int)$totals['visits'],
            'pages' => $this->db->select('path', 'SUM(views) AS views', 'SUM(visits) AS visits')->from('statistics_daily')->where('path !=', '')->where('day >=', $start)->group_by('path')->order_by('views DESC')->limit(15)->get(),
            'daily' => $this->db->select('day', 'views', 'visits')->from('statistics_daily')->where('path', '')->where('day >=', $start)->order_by('day')->get()];
    }
}
