<?php
chdir($argv[1] ?? dirname(__DIR__));
define('HIDDENCMS_CLI', TRUE);
require 'index.php';
function dashboard_check($value, $label) {
    if (!$value) throw new RuntimeException($label);
    echo "PASS $label\n";
}
$data = HB()->module('admin')->model('dashboard')->data();
dashboard_check(is_int($data['new_members']) && $data['new_members'] >= 0, 'Member count');
dashboard_check(is_bool($data['smtp']) && is_bool($data['maintenance']), 'Site states');
dashboard_check($data['updates'] === NULL || is_int($data['updates']), 'Cached update count');
foreach ($data['contents'] as $group) {
    dashboard_check(HB()->module($group['module'])->is_enabled(), 'Active module: '.$group['module']);
    dashboard_check(count($group['rows']) <= 3, 'Recent content limit: '.$group['module']);
    foreach ($group['rows'] as $row) dashboard_check(strpos($row['url'], 'admin/'.$group['module'].'/') === 0 && $row['title'] !== '', 'Content edit link');
}
dashboard_check($data['traffic'] === NULL || isset($data['traffic']['visits'], $data['traffic']['views']), 'Traffic summary');
