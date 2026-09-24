<?php
$root = $argv[1] ?? dirname(__DIR__);
chdir($root); define('HIDDENCMS_CLI', TRUE); require 'index.php';
$codec = HB()->disposition;
$rows = [['style' => 'row-default', 'cols' => [['size' => 'col-12', 'widgets' => []]]]];
$legacy = json_encode($rows, JSON_UNESCAPED_SLASHES);
$wrapped = json_encode(['zone_classes' => 'no-padding custom', 'rows' => $rows]);
foreach ([$legacy, $wrapped] as $value) {
    if ($codec->encode($codec->decode($value)) !== $legacy) throw new RuntimeException('Layout compatibility failed');
    echo "PASS Layout contents preserved without zone helpers\n";
}
if ($codec->encode($codec->decode('{"zone_classes":"no-padding","rows":[]}')) !== '[]') throw new RuntimeException('Empty layout failed');
echo "PASS Empty layout preserved\n";

$settings = HB()->module('live_editor')->model()->parse_widget_settings_form('settings%5Bslider_id%5D=2&settings%5Bfull_width%5D=0&settings%5Bfull_width%5D=1');
if ($settings !== ['slider_id' => '2', 'full_width' => '1']) throw new RuntimeException('Widget settings form parsing failed');
echo "PASS Widget settings form envelope removed\n";
