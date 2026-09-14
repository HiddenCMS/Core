<?php
$root = $argv[1] ?? dirname(__DIR__);
chdir($root);
define('HIDDENCMS_CLI', TRUE);
require 'index.php';
foreach (['comments', 'pages', 'search'] as $name) {
    $module = HB()->module($name);
    $settings = $module->settings();
    $previous = $settings->enabled;
    try {
        $settings->set('enabled', FALSE);
        if ($module->is_enabled()) throw new RuntimeException($name.' ignores disabled state');
        $settings->set('enabled', TRUE);
        if (!$module->is_enabled()) throw new RuntimeException($name.' cannot be enabled');
        echo "PASS $name disable/enable in memory\n";
    } finally {
        $settings->set('enabled', $previous);
    }
}
$admin = HB()->module('admin');
$settings = $admin->settings();
$previous = $settings->enabled;
try {
    $settings->set('enabled', FALSE);
    if (!$admin->is_enabled()) throw new RuntimeException('Required admin module was disabled');
    echo "PASS mandatory admin remains enabled\n";
} finally {
    $settings->set('enabled', $previous);
}
