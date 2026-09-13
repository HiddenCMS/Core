<?php
namespace HB\Modules\Statistics\Controllers;
use HB\HiddenCMS\Loadables\Controllers\Module_Checker;
class Ajax_Checker extends Module_Checker
{
    public function view()
    {
        $post = $_POST;
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || ($this->config->statistics_enabled ?? '1') !== '1' || $this->user->admin || is_crawler() ||
            ($post['consent'] ?? '') !== '1' || !is_string($post['token'] ?? NULL) ||
            !hash_equals($this->form()->token('statistics-view'), $post['token'])) return;
        $path = $this->model('traffic')->path($post['path'] ?? NULL);
        if ($path === NULL || !$this->model('traffic')->ready()) return;
        $last = $this->session('statistics', 'last_view');
        if ($last && time() - $last['time'] < 2) return;
        $this->session->set('statistics', 'last_view', ['time' => time()]);
        $crypt = []; include 'config/crypt.php';
        $visitor = hash_hmac('sha256', date('Y-m-d').':'.$this->session->id, $crypt['key']);
        $this->extension('json');
        return [$path, $visitor];
    }
}
