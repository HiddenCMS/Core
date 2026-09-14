<?php
chdir($argv[1] ?? dirname(__DIR__));
define('HIDDENCMS_CLI', TRUE);
require 'index.php';
function auth_check($value, $label) { if (!$value) throw new RuntimeException($label); echo "PASS $label\n"; }
$html = (string)HB()->module('user')->view('login', ['form' => '', 'authenticators' => HB()->array()]);
auth_check(strpos($html, 'fa-arrow-left') !== FALSE, 'Login home link rendered through theme override');
HB()->session->set('auth_return', '/fr/titi?tab=2');
auth_check(user_login_destination() === '/fr/titi?tab=2', 'Local destination preserved');
auth_check(user_login_destination() === HB()->url(), 'Return consumed once');
HB()->session->set('auth_return', '//evil.test');
auth_check(user_login_destination() === HB()->url(), 'Unsafe stored destination rejected');
$old = $_SERVER['REQUEST_URI'] ?? '';
$_SERVER['REQUEST_URI'] = '/fr/titi?tab=2';
auth_check(strpos(url('user/login'), 'return=%2Ffr%2Ftiti%3Ftab%3D2') !== FALSE, 'Login link carries original page');
$_SERVER['REQUEST_URI'] = $old;
$html = (string)HB()->widget('language')->output('index');
auth_check(strpos($html, 'hreflang="en"') !== FALSE && strpos($html, 'hreflang="fr"') !== FALSE, 'Enabled language links rendered');
auth_check(substr_count($html, 'aria-current="true"') === 1, 'Current language identified');
$route = HB()->url->request === 'index' ? '' : HB()->url->request;
auth_check(strpos($html, '/en'.($route !== '' ? '/'.$route : '').HB()->url->query) !== FALSE, 'Language switch preserves current route and query');
