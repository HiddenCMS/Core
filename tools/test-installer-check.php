<?php

namespace HiddenCMSInstallerCheckTest;

// Run the real endpoint without sending mail, writing .htaccess or making HTTP requests.
function copy($source, $destination) { return false; }
function mail($to, $subject, $message) { return false; }
function extension_loaded($name) { return $name === 'curl' || \extension_loaded($name); }
function curl_init() { return null; }
function curl_setopt($handle, $option, $value) { return true; }
function curl_exec($handle) { return ''; }
function curl_close($handle) {}
function header($value) {}
function http_response_code($code) { $GLOBALS['installer_check_status'] = $code; }
function lang($message) { return $message; }

define('HIDDENCMS_CMS', true);
$_GET = ['step' => 'check'];
$_SERVER['HTTP_REFERER'] = 'https://example.test/index.php';
$GLOBALS['installer_check_status'] = 200;

$source = file_get_contents(__DIR__.'/../install/ajax.php');
ob_start();
eval('namespace HiddenCMSInstallerCheckTest; '.substr($source, 5));
$response = json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);

if ($GLOBALS['installer_check_status'] !== 200 || !is_array($response) || isset($response['error'])) {
    throw new \RuntimeException('System checks failed without legacy version constants.');
}

$titles = array_column($response, 'title');
if (!in_array('HTTP compression', $titles, true) || in_array('Check for updates', $titles, true)) {
    throw new \RuntimeException('Unexpected installer checks.');
}

echo "Installer system checks passed without legacy version constants.\n";
