<?php

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
chdir(dirname(__DIR__));
define('HIDDENCMS_CLI', TRUE);
$argv[1] = $argv[1] ?? '--tests';
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'index.php';
set_exception_handler(function($error){ fwrite(STDERR, $error.PHP_EOL); exit(1); });

// All configuration changes below are in memory; no site setting is written.
HB()->config->analytics = 'G-TEST12345';
if (in_array('--fixture', $argv, TRUE))
{
	echo '<!doctype html><html><head></head><body><footer>'.privacy_preferences_link().'</footer>';
	echo privacy_embed('youtube', 'https://www.youtube-nocookie.com/embed/abcdefghijk');
	echo HB()->view('theme/privacy');
	echo '</body></html>';
	exit;
}
$checks = 0;
$assert = function($condition, $message) use (&$checks){
	if (!$condition) throw new RuntimeException($message);
	$checks++;
	echo 'PASS '.$message.PHP_EOL;
};
$assert(privacy_analytics_id() === 'G-TEST12345', 'GA4 ID is accepted');
$url_state = new ReflectionProperty(HB()->url, '_const');
$url_state->setAccessible(TRUE);
$original_url = $url_state->getValue(HB()->url);
$admin_url = $original_url;
$admin_url['admin'] = TRUE;
$url_state->setValue(HB()->url, $admin_url);
$assert(privacy_analytics_id() === '' && !isset(privacy_services()['analytics']), 'Administration never enables Analytics');
$url_state->setValue(HB()->url, $original_url);
foreach (['UA-123-4', 'G-ABC" onload="x', '', "G-ABC\nINVALID"] as $id)
{
	HB()->config->analytics = $id;
	$assert(privacy_analytics_id() === '', 'Legacy/invalid/empty analytics ID cannot load');
}
HB()->config->analytics = 'G-TEST12345';
$html = privacy_embed('youtube', 'https://www.youtube-nocookie.com/embed/abcdefghijk', '<b>Video</b>');
$assert(strpos($html, '<iframe') === FALSE && strpos($html, '<img') === FALSE, 'Placeholder has no remote frame or thumbnail');
$assert(strpos($html, '&lt;b&gt;Video&lt;/b&gt;') !== FALSE, 'Embed title is escaped');
foreach (['javascript:alert(1)', 'https://www.youtube.com.evil.test/embed/id', 'https://evil.test', 'http://www.youtube.com/embed/id', 'https://user@www.youtube.com/embed/id', 'https://www.youtube.com:8443/embed/id'] as $url)
{
	$assert(privacy_embed('youtube', $url) === '', 'Unsafe embed URL is rejected: '.$url);
}
$assert(privacy_embed('unknown', 'https://www.youtube.com/embed/id') === '', 'Unknown service cannot embed');
$video = (string)HB()->bbcode->bbcode2html('[video]abcdefghijk[/video]');
$assert(strpos($video, 'data-privacy-service="youtube"') !== FALSE && strpos($video, '<iframe') === FALSE, 'BBCode video is gated on the server');
foreach (['https://www.youtube.com', '//www.youtube-nocookie.com', 'http://youtube.com', 'https://WWW.YOUTUBE.COM'] as $host)
{
	$rich = privacy_media_html('<p>Avant &amp; apr&egrave;s</p><iframe src="'.$host.'/embed/abcdefghijk" title="Essai" onload="alert(1)"></iframe><p>Fin</p>');
	$assert(strpos($rich, '<iframe') === FALSE && strpos($rich, 'data-privacy-service="youtube"') !== FALSE, 'Rich-text video is gated: '.$host);
	$assert(strpos($rich, 'onload') === FALSE && strpos($rich, '<p>Fin</p>') !== FALSE, 'Unsafe frame attributes are dropped and surrounding content preserved');
}
$rich = bbcode('<iframe src="https://youtube.com/embed/abcdefghijk"></iframe>');
$assert(strpos($rich, '<iframe') === FALSE, 'Rich content entry point applies filtering');
$unchanged = '<p>Un texte <strong>normal</strong></p>';
$assert(privacy_media_html($unchanged) === $unchanged, 'Text without frames is byte-for-byte unchanged');
$unchanged = '<iframe src="/local-preview"></iframe>';
$assert(privacy_media_html($unchanged) === $unchanged, 'Internal previews are unchanged');
$multiple = privacy_media_html('<iframe src="https://youtube.com/embed/abcdefghijk"></iframe><iframe src="https://youtube.com/embed/lmnopqrstuv"></iframe>');
$assert(substr_count($multiple, 'data-privacy-service="youtube"') === 2, 'Every video in a rich fragment is gated');
$view = (string)HB()->view('theme/privacy');
$assert(strpos($view, 'checked') === FALSE, 'No service is prechecked in HTML');
$assert(strpos($view, '<script async src="https://') === FALSE, 'Preferences HTML contains no live analytics tag');
$assert(strpos($view, 'data-privacy-choice="reject"') !== FALSE && strpos($view, 'data-privacy-choice="accept"') !== FALSE, 'Accept and reject both exist at the first level');
privacy_services('example-media', ['title' => 'Example', 'description' => 'Example media provider', 'hosts' => ['media.example.test']]);
$assert(strpos(privacy_embed('example-media', 'https://media.example.test/embed/1'), 'data-privacy-service="example-media"') !== FALSE, 'Addon can declare and gate a service');
$assert(strpos(privacy_media_html('<iframe src="https://media.example.test/embed/1"></iframe>'), '<iframe') === FALSE, 'Declared addon media also uses rich-text filtering');
$assert(strpos(file_get_contents('hiddencms/views/theme/analytics.tpl.php'), '<script') === FALSE, 'Legacy analytics template cannot bypass consent');
echo $checks.' checks passed.'.PHP_EOL;
