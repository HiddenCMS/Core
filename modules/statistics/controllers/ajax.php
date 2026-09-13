<?php
namespace HB\Modules\Statistics\Controllers;
use HB\HiddenCMS\Loadables\Controllers\Module as Controller_Module;
class Ajax extends Controller_Module
{
    public function view($path, $visitor)
    {
        try { $this->model('traffic')->record($path, $visitor); }
        catch (\Throwable $error) { error_log('[Statistics] Page view could not be recorded.'); return $this->json(['success' => FALSE]); }
        return $this->json(['success' => TRUE]);
    }
}
