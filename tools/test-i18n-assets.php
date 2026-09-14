<?php
chdir($argv[1]);
define('HIDDENCMS_CLI', TRUE);
require 'index.php';
set_exception_handler(function($error){ fwrite(STDERR, $error->getMessage()."\n".$error->getTraceAsString()."\n"); exit(1); });
$language = $argv[2];
foreach (HB()->config->langs as $candidate) {
    if ($candidate->info()->name === $language) { HB()->config->lang = $candidate; HB()->config->langs = [$candidate]; break; }
}
$scripts = [];
$render = function($path) { ob_start(); include $path; return ob_get_clean(); };
foreach ([
    'modules/settings/js/admin/maintenance.js'=>'settings',
    'modules/access/js/access.js'=>'access',
    'modules/live_editor/js/live-editor.js'=>'live_editor',
    'modules/comments/js/comments.js'=>'comments',
    'modules/files/js/file_manager.js'=>'files',
    'themes/admin/js/update.js'=>'admin',
] as $path=>$module) $scripts[$path] = $render->call(HB()->module($module), $path);
foreach ([
    'calendar'=>'C:/wamp64/www/hiddencms-calendar/modules/calendar/js/calendar.js',
    'slider'=>'C:/wamp64/www/hiddencms-slider/modules/slider/js/admin.js',
] as $module=>$path) if (is_file($path)) $scripts[$path] = $render->call(HB()->module($module) ?: HB(), $path);
$path = 'C:/wamp64/www/hiddencms-altitude/themes/altitude/js/altitude.js';
if (is_file($path)) $scripts[$path] = $render->call(HB()->theme('altitude') ?: HB(), $path);
$path = 'C:/wamp64/www/hiddencms-slider/widgets/slider/js/slider.js';
if (is_file($path)) $scripts[$path] = $render->call(HB()->widget('slider') ?: HB(), $path);
$html = (string)HB()->view('theme/privacy');
if (!preg_match('~<script[^>]*id="privacy-config"[^>]*>(.*?)</script>~s', $html, $match)) throw new RuntimeException('Missing privacy configuration');
$scripts['privacy-config'] = json_decode($match[1], TRUE, 512, JSON_THROW_ON_ERROR);
echo json_encode($scripts, JSON_THROW_ON_ERROR);
