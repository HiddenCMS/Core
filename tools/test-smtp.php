<?php
$root = $argv[1] ?? dirname(__DIR__);
chdir($root);
define('HIDDENCMS_CLI', TRUE);
require 'index.php';
function check_smtp($value, $label) { if (!$value) throw new RuntimeException($label); echo "PASS $label\n"; }
$db = HB()->db; $model = HB()->module('settings')->model('smtp');
$before = [];
foreach (array_merge(array_keys($model->values()), ['password']) as $key) $before['smtp_'.$key] = HB()->config->{'smtp_'.$key} ?? NULL;
$db->begin_transaction();
try {
    $data = ['smtp_enabled' => '1', 'smtp_host' => 'localhost', 'smtp_port' => '587', 'smtp_secure' => 'tls',
        'smtp_username' => 'test@example.test', 'smtp_password' => 'secret&amp;&lt;&gt;', 'smtp_clear_password' => '0',
        'smtp_from' => 'sender@example.test', 'smtp_name' => 'Test sender'];
    $model->save($data);
    $secret = HB()->config->smtp_password;
    check_smtp($secret !== 'secret&<>' && !str_contains($secret, 'secret'), 'Password encrypted');
    check_smtp($model->transport()['password'] === 'secret&<>', 'Password decrypted with special characters');
    $data['smtp_password'] = '';
    $model->save($data);
    check_smtp(HB()->config->smtp_password === $secret, 'Empty password retains secret');
    $email = HB()->email;
    $config = new ReflectionProperty($email, '_config'); $config->setAccessible(TRUE);
    check_smtp($config->getValue($email)['smtp']['host'] === 'localhost', 'Email uses saved SMTP settings');
    foreach (['smtp_port' => '65536', 'smtp_secure' => 'invalid', 'smtp_host' => 'host;evil', 'smtp_enabled' => '2', 'smtp_from' => 'invalid'] as $key => $value) {
        $invalid = $data; $invalid[$key] = $value;
        try { $model->save($invalid); check_smtp(FALSE, 'Invalid setting rejected'); }
        catch (InvalidArgumentException $error) { check_smtp(TRUE, 'Invalid setting rejected: '.$key); }
    }
    HB()->config->smtp_password = base64_encode(random_bytes(40));
    try { $model->transport(); check_smtp(FALSE, 'Tampered secret rejected'); }
    catch (RuntimeException $error) { check_smtp(TRUE, 'Tampered secret rejected'); }
    HB()->config->smtp_password = $secret;
    $data['smtp_clear_password'] = '1'; $model->save($data);
    check_smtp($model->transport()['password'] === '', 'Secret can be removed');
    $data['smtp_enabled'] = '0'; $model->save($data);
    check_smtp($model->transport()['host'] === '', 'PHP mail fallback');
} finally {
    $db->rollback();
    foreach ($before as $key => $value) { if ($value === NULL) HB()->config->unset($key); else HB()->config->$key = $value; }
}
echo "Database changes rolled back.\n";
