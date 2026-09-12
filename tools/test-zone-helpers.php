<?php
$root = $argv[1] ?? dirname(__DIR__);
chdir($root); define('HIDDENCMS_CLI', TRUE); require 'index.php';
$codec = HB()->disposition;
function check_zone($value, $name) { if (!$value) { throw new RuntimeException($name); } echo "PASS $name\n"; }
$legacy = '[{"style":"row-default","cols":[]}]';
$rows = $codec->decode($legacy);
check_zone($codec->encode($rows) === $legacy, 'Legacy layout round trip');
$rows->zone_classes = 'no-padding parent-width client-zone no-padding "bad" <script>';
$encoded = $codec->encode($rows);
$decoded = $codec->decode($encoded);
check_zone($decoded->zone_classes === 'no-padding parent-width client-zone', 'Classes normalized and deduplicated');
check_zone($decoded->count() === 1, 'Metadata is not a row');
$decoded[0]->style('row-updated');
check_zone($codec->decode($codec->encode($decoded))->zone_classes === $decoded->zone_classes, 'Row edits preserve zone helpers');
$decoded->zone_classes = '';
check_zone(!isset(json_decode($codec->encode($decoded), TRUE)['rows']), 'Clearing helpers restores legacy layout');
$empty = $codec->decode(''); $empty->zone_classes = 'no-margin';
check_zone($codec->decode($codec->encode($empty))->zone_classes === 'no-margin', 'Empty zones support helpers');
$db = HB()->db; $db->begin_transaction();
try {
    $record = $db->from('dispositions')->row();
    if (!$record) throw new RuntimeException('Test site needs a disposition');
    $model = HB()->module('live_editor')->model();
    $layout = $model->get_disposition($record['disposition_id'], $theme, $page, $zone);
    $layout->zone_classes = 'no-padding client-zone';
    $model->set_disposition($record['disposition_id'], $layout);
    $saved = $db->from('dispositions')->where('disposition_id', (int)$record['disposition_id'])->row();
    check_zone($model->get_disposition($record['disposition_id'], $theme, $page, $zone)->zone_classes === 'no-padding client-zone', 'Zone helpers persist in database');
    $html = (string)HB()->zone()->display($saved);
    check_zone(strpos($html, 'class="hc-zone no-padding client-zone"') !== FALSE, 'Front zone wrapper receives classes');
} finally { $db->rollback(); echo "Database changes rolled back.\n"; }
