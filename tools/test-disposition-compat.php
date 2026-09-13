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
