<?php
namespace HB\Widgets\Language\Controllers;
use HB\HiddenCMS\Loadables\Controllers\Widget as Controller_Widget;
class Index extends Controller_Widget
{
    public function index($settings = [])
    {
        $languages = [];
        $flags = ['en' => 'gb', 'fr' => 'fr', 'de' => 'de', 'es' => 'es', 'it' => 'it', 'pt' => 'pt'];
        foreach (HB()->model2('addon')->get('language') as $language) {
            if (!$language->is_enabled()) continue;
            $code = $language->info()->name;
            $request = $this->url->request === 'index' ? '' : $this->url->request;
            $path = rtrim($this->url->base.$code, '/').($request !== '' ? '/'.$request : '').$this->url->query;
            $languages[] = ['title' => $language->info()->title, 'code' => $code, 'url' => $path, 'flag' => isset($flags[$code]) ? image('flags/'.$flags[$code].'.png') : NULL, 'active' => $code === $this->config->lang->info()->name];
        }
        if (count($languages) < 2) return '';
        return $this->css('language')->js('language')->view('index', compact('languages'));
    }
}
