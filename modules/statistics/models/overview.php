<?php
namespace HB\Modules\Statistics\Models;

use HB\HiddenCMS\Loadables\Model;

class Overview extends Model
{
    const PERIODS = [7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days', 365 => 'Last 12 months'];

    public function period($value)
    {
        return is_scalar($value) && array_key_exists((string)$value, self::PERIODS) ? (int)$value : 30;
    }

    public function snapshot($days, $today = NULL)
    {
        $days = $this->period($days);
        $end = ($today ? clone $today : new \DateTimeImmutable('today'))->modify('+1 day')->setTime(0, 0);
        $start = $end->modify('-'.$days.' days');
        $previous = $start->modify('-'.$days.' days');
        $tables = $this->db->tables();
        $metrics = [];
        $series = [];
        $definitions = [
            'registrations' => ['label' => 'New members', 'icon' => 'fas fa-user-plus', 'color' => '#1696a5', 'table' => 'user', 'date' => 'registration_date'],
            'connections' => ['label' => 'Members signed in', 'icon' => 'fas fa-users', 'color' => '#4278c4', 'table' => 'session_history', 'date' => 'date'],
            'comments' => ['label' => 'Comments', 'icon' => 'fas fa-comments', 'color' => '#b36d24', 'table' => 'comments', 'date' => 'date'],
            'uploads' => ['label' => 'Media added', 'icon' => 'fas fa-images', 'color' => '#4d9364', 'table' => 'file', 'date' => 'date']
        ];
        foreach ($definitions as $key => $definition) {
            $definition['label'] = (string)$this->lang($definition['label']);
            if (!in_array($definition['table'], $tables, TRUE)) continue;
            $current = $this->activity($key, $definition, $start, $end);
            $before = $this->activity($key, $definition, $previous, $start);
            $metrics[$key] = $definition + ['value' => $current, 'previous' => $before,
                'change' => $before ? round(($current - $before) / $before * 100) : NULL];
            $query = $this->activity_query($key, $definition);
            $date = 's.'.$definition['date'];
            $rows = $query->select('DATE('.$date.') AS day', ($key === 'connections' ? 'COUNT(DISTINCT s.user_id)' : 'COUNT(*)').' AS total')
                ->where($date.' >=', $start->format('Y-m-d H:i:s'))->where($date.' <', $end->format('Y-m-d H:i:s'))->group_by('day')->order_by('day')->get();
            $counts = array_column($rows, 'total', 'day');
            $points = [];
            for ($date = $start; $date < $end; $date = $date->modify('+1 day')) $points[] = [$date->format('Y-m-d'), (int)($counts[$date->format('Y-m-d')] ?? 0)];
            $series[] = ['key' => $key, 'name' => $key === 'connections' ? (string)$this->lang('Members signed in per day') : $definition['label'], 'color' => $definition['color'], 'data' => $points];
        }
        $inventory = [];
        foreach ([
            ['pages', 'Pages', 'fas fa-file-alt', 'pages'], ['news', 'News', 'fas fa-newspaper', 'news'],
            ['gallery', 'Galleries', 'fas fa-images', 'gallery'], ['calendar_events', 'Events', 'fas fa-calendar-alt', 'calendar'],
            ['slider_sets', 'Sliders', 'fas fa-clone', 'slider']
        ] as [$table, $label, $icon, $module]) {
            $label = (string)$this->lang($label);
            if (!in_array($table, $tables, TRUE)) continue;
            $addon = @HB()->module($module);
            if (!$addon || !$addon->is_enabled()) continue;
            $total = (int)$this->db->from($table)->count();
            $published = (int)$this->db->from($table)->where('published', '1')->count();
            $inventory[] = compact('label', 'icon', 'module', 'total', 'published');
        }
        return ['days' => $days, 'start' => $start->format('d/m/Y'), 'end' => $end->modify('-1 day')->format('d/m/Y'),
            'metrics' => $metrics, 'series' => $series, 'inventory' => $inventory,
            'members' => (int)$this->db->from('user')->where('deleted', FALSE)->count(),
            'media' => in_array('file', $tables, TRUE) ? (int)$this->db->from('file')->count() : 0];
    }

    private function activity_query($key, $definition)
    {
        $query = $this->db->from($definition['table'].' s');
        if ($key === 'registrations') $query->where('s.deleted', FALSE);
        if ($key === 'connections') $query->join('user u', 'u.id = s.user_id')->where('u.deleted', FALSE);
        return $query;
    }

    private function activity($key, $definition, $start, $end)
    {
        $query = $this->activity_query($key, $definition);
        return (int)$query->select($key === 'connections' ? 'COUNT(DISTINCT s.user_id)' : 'COUNT(*)')
            ->where('s.'.$definition['date'].' >=', $start->format('Y-m-d H:i:s'))
            ->where('s.'.$definition['date'].' <', $end->format('Y-m-d H:i:s'))->row();
    }

    public function csv($snapshot)
    {
        $stream = fopen('php://temp', 'r+');
        $header = ['Date']; foreach ($snapshot['series'] as $series) $header[] = $series['name'];
        fputcsv($stream, $header, ';', '"', '');
        for ($i = 0; $i < $snapshot['days']; $i++) {
            $row = [$snapshot['series'][0]['data'][$i][0]];
            foreach ($snapshot['series'] as $series) $row[] = $series['data'][$i][1];
            fputcsv($stream, $row, ';', '"', '');
        }
        rewind($stream); $csv = "\xEF\xBB\xBF".stream_get_contents($stream); fclose($stream);
        return $csv;
    }
}
