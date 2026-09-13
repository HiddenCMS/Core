<?php
namespace HB\Modules\Admin\Models;
use HB\HiddenCMS\Loadables\Model;

class Dashboard extends Model
{
    public function data()
    {
        $tables = $this->db->tables();
        $lang = $this->config->lang->info()->name;
        $definitions = [
            'pages' => ['Pages', 'fas fa-file-alt', 'pages', 'page_id', 'pages_lang', 'page_id'],
            'news' => ['Actualités', 'fas fa-newspaper', 'news', 'news_id', 'news_lang', 'news_id'],
            'gallery' => ['Galeries', 'fas fa-images', 'gallery', 'gallery_id', 'gallery_lang', 'gallery_id'],
            'calendar' => ['Événements', 'fas fa-calendar-alt', 'calendar_events', 'event_id', NULL, NULL],
            'slider' => ['Sliders', 'fas fa-clone', 'slider_sets', 'slider_id', NULL, NULL]
        ];
        $contents = []; $tasks = [];
        foreach ($definitions as $module => [$label, $icon, $table, $id, $translation, $foreign]) {
            $addon = @HB()->module($module);
            if (!$addon || !$addon->is_enabled() || !in_array($table, $tables, TRUE) || ($translation && !in_array($translation, $tables, TRUE))) continue;
            $query = $this->db->select('s.'.$id.' AS id', 's.published', $translation ? 'l.title' : 's.title')->from($table.' s');
            if ($translation) $query->join($translation.' l', 'l.'.$foreign.' = s.'.$id.' AND l.lang = "'.$this->db->escape_string($lang).'"', 'LEFT');
            $rows = $query->order_by('s.'.$id.' DESC')->limit(3)->get();
            foreach ($rows as &$row) {
                $row['title'] = $row['title'] ?: $label.' #'.$row['id'];
                $row['url'] = in_array($module, ['calendar', 'slider'], TRUE) ? 'admin/'.$module.'/edit/'.$row['id'] : 'admin/'.$module.'/'.$row['id'].'/'.url_title($row['title']);
            }
            unset($row);
            $contents[] = compact('module', 'label', 'icon', 'rows');
            $unpublished = (int)$this->db->from($table)->where('published', '0')->count();
            if ($unpublished) $tasks[] = ['title' => $label.' non publiés / inactifs', 'count' => $unpublished, 'icon' => $icon, 'url' => 'admin/'.$module];
        }
        $requests = [];
        if (in_array('user_erasure_request', $tables, TRUE)) {
            $pending = (int)$this->db->from('user_erasure_request')->where('completed_at', NULL)->count();
            if ($pending) {
                $tasks[] = ['title' => 'Demandes d’effacement en attente', 'count' => $pending, 'icon' => 'fas fa-user-shield', 'url' => 'admin/settings/privacy'];
                $requests = $this->db->select('r.user_id', 'r.execute_after', 'u.username')->from('user_erasure_request r')->join('user u', 'u.id = r.user_id')->where('r.completed_at', NULL)->order_by('r.execute_after')->limit(5)->get();
            }
        }
        $traffic = NULL;
        $statistics = @HB()->module('statistics');
        if ($statistics && $statistics->is_enabled()) $traffic = $statistics->model('traffic')->report(30);
        $updates = $this->core_updater->cached_status();
        $update_count = $updates ? count($updates['addons'] ?? []) + (!empty($updates['core']['available']) ? 1 : 0) : NULL;
        if ($updates && (!empty($updates['core']['error']) || !empty($updates['addons_error']))) $update_count = NULL;
        $smtp = HB()->module('settings')->model('smtp')->values();
        return ['contents' => $contents, 'tasks' => $tasks, 'requests' => $requests, 'traffic' => $traffic,
            'new_members' => (int)$this->db->from('user')->where('deleted', FALSE)->where('registration_date >=', (new \DateTimeImmutable('tomorrow'))->modify('-30 days')->format('Y-m-d H:i:s'))->count(),
            'updates' => $update_count, 'checked_at' => $updates['checked_at'] ?? NULL,
            'maintenance' => (bool)$this->config->maintenance,
            'smtp' => (int)$smtp['enabled'] === 1,
            'smtp_host' => !empty($smtp['host']),
            'has_pages' => in_array('pages', array_column($contents, 'module'), TRUE)];
    }
}
