<?php
chdir($argv[1] ?? dirname(__DIR__)); define('HIDDENCMS_CLI', TRUE); require 'index.php';
function stats_check($value, $label) { if (!$value) throw new RuntimeException($label); echo "PASS $label\n"; }
$db = HB()->db; $overview = HB()->module('statistics')->model('overview'); $traffic = HB()->module('statistics')->model('traffic');
if (!$traffic->ready()) {
    $migration = require __DIR__.'/../hiddencms/migrations/0.7.2.php'; $migration->up($db);
    $db->insert_checked('core_migrations', ['migration' => '0.7.2', 'version' => '0.7.2', 'batch' => (int)$db->select('MAX(batch)')->from('core_migrations')->row() + 1, 'applied_at' => date('Y-m-d H:i:s')]);
    echo "Applied local traffic migration.\n";
}
foreach ([NULL, [], 'evil', '9999', '-1'] as $value) stats_check($overview->period($value) === 30, 'Invalid period falls back');
foreach ([7,30,90,365] as $days) {
    $s = $overview->snapshot($days);
    stats_check(count($s['series'][0]['data']) === $days, 'Complete daily series: '.$days);
    stats_check(count(explode("\n", trim($overview->csv($s)))) === $days + 1, 'CSV daily rows: '.$days);
}
foreach (['//evil.test', '/fr/admin/pages', '/fr/user/reset-password/token', '/fr/files/1', '/fr/page?token=secret', '/fr/%61dmin', '/fr/page%3ftoken=x', '/fr/../admin'] as $path) stats_check($traffic->path($path) === NULL, 'Private/unsafe path rejected');
stats_check($traffic->path('/fr/parent/enfant/') === '/fr/parent/enfant', 'Public page normalized');
$db->begin_transaction();
try {
    $visitor = hash('sha256', random_bytes(32)); $path = '/fr/statistics-test-'.bin2hex(random_bytes(8));
    $before = $traffic->report(7);
    stats_check($traffic->record($path, $visitor, NULL, FALSE), 'Page view recorded');
    $traffic->record($path, $visitor, NULL, FALSE);
    $page = $db->select('views', 'visits')->from('statistics_daily')->where('day', date('Y-m-d'))->where('path', $path)->row();
    stats_check((int)$page['views'] === 2 && (int)$page['visits'] === 1, 'Same session: two views, one visit');
    $traffic->record($path.'/other', $visitor, NULL, FALSE);
    $after = $traffic->report(7);
    stats_check($after['visits'] === $before['visits'] + 1 && $after['views'] === $before['views'] + 3, 'Global visit not duplicated across pages');
    $traffic->record($path, hash('sha256', random_bytes(32)), NULL, FALSE);
    $page = $db->select('views', 'visits')->from('statistics_daily')->where('day', date('Y-m-d'))->where('path', $path)->row();
    stats_check((int)$page['views'] === 3 && (int)$page['visits'] === 2, 'Second session counted');
} finally { $db->rollback(); }
echo "Test counts rolled back.\n";
