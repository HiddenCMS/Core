<?php
if (PHP_SAPI !== 'cli' || empty($argv[1])) exit("Usage: php tools/test-i18n-install.php runtime-directory\n");
$sql = file_get_contents(dirname(__DIR__).'/install/DATABASE.sql');
require dirname(__DIR__).'/install/language.php';
chdir($argv[1]);
define('HIDDENCMS_CLI', TRUE);
require 'index.php';
set_exception_handler(function($error){ fwrite(STDERR, $error->getMessage()."\n".$error->getTraceAsString()."\n"); exit(1); });
$db = HB()->db;
foreach ($argv as $arg) if (preg_match('/^--cleanup-empty=(hb_test_install_[a-f0-9]{12})$/D', $arg, $match)) {
    $tables = $db->select('COUNT(*)')->from('information_schema.TABLES')->where('TABLE_SCHEMA', $match[1])->row();
    if ((int)$tables !== 0) throw new RuntimeException('Refusing to delete a nonempty test database');
    $db->execute_checked('DROP DATABASE `'.$match[1].'`');
    echo "Empty temporary test database removed\n";
    exit;
}
if (in_array('--list-test-databases', $argv, TRUE)) {
    print_r($db->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME LIKE 'hb_test_install_%'")->get());
    exit;
}
$original = $db->query('SELECT DATABASE()')->row();
$temporary = 'hb_test_install_'.bin2hex(random_bytes(6));
$quote = function($name){ return '`'.str_replace('`','``',$name).'`'; };
$reset = function() use ($db){ $property = new ReflectionProperty($db->driver(), 'stmt'); $property->setAccessible(TRUE); $property->setValue($db->driver(), []); };
$db->execute_checked('CREATE DATABASE '.$quote($temporary).' CHARACTER SET utf8mb4');
try {
    $db->execute_script('USE '.$quote($temporary)); $reset();
    $db->execute_script($sql);
    $comments = json_decode($db->select('data')->from('addon')->where('name','comments')->where('type_id',1)->row(), TRUE);
    if ($comments['enabled'] !== FALSE) throw new RuntimeException('Comments enabled on fresh installation');
    echo "PASS comments disabled by default\n";
    $english = json_decode($db->select('data')->from('addon')->where('name','en')->row(), TRUE);
    $french = json_decode($db->select('data')->from('addon')->where('name','fr')->row(), TRUE);
    if (!$english['enabled'] || !$french['enabled'] || $english['order'] >= $french['order']) throw new RuntimeException('English is not the fresh-install default');
    if ($db->select('name')->from('pages')->where('page_id',1)->row() !== 'home') throw new RuntimeException('Home slug is not English');
    foreach (['en'=>'Home','fr'=>'Accueil'] as $language=>$title) {
        if ($db->select('title')->from('pages_lang')->where('page_id',1)->where('lang',$language)->row() !== $title) throw new RuntimeException('Missing home translation: '.$language);
    }
    echo "PASS fresh database imports with English first, French enabled and both home translations\n";
    $connection = new ReflectionProperty($db->driver(), 'db');
    $connection->setAccessible(TRUE);
    $mysqli = $connection->getValue($db->driver());
    foreach (['fr', 'en'] as $language) {
        install_language($mysqli, $language);
        $first = $mysqli->query("SELECT name FROM addon WHERE type_id=4 ORDER BY CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.order')) AS UNSIGNED) LIMIT 1")->fetch_row()[0];
        if ($first !== $language) throw new RuntimeException('Primary language selection failed: '.$language);
        echo "PASS primary language selection: $language\n";
    }
    try {
        install_language($mysqli, 'invalid');
        throw new RuntimeException('Unsupported language was accepted');
    } catch (InvalidArgumentException $error) {
        echo "PASS unsupported language rejected\n";
    }
} finally {
    $db->execute_checked('SET FOREIGN_KEY_CHECKS=1');
    $db->execute_script('USE '.$quote($original)); $reset();
    $db->execute_checked('DROP DATABASE '.$quote($temporary));
}
