<?php

use HB\HiddenCMS\Core\Url;

$checks = 0;
$assert = function($condition, $message) use (&$checks){
	if (!$condition)
	{
		throw new RuntimeException($message);
	}

	$checks++;
	echo 'PASS '.$message.PHP_EOL;
};

require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/hiddencms/hiddencms.php';
require_once dirname(__DIR__).'/hiddencms/core.php';
require_once dirname(__DIR__).'/hiddencms/core/url.php';

$assert(Url::maintenance_access_allowed(TRUE, [], []), 'Administrators always retain maintenance access');
$assert(!Url::maintenance_access_allowed(FALSE, [], ['members']), 'Visitors cannot use an allowed member group');
$assert(!Url::maintenance_access_allowed(FALSE, NULL, ['members']), 'Unavailable groups are treated as an anonymous visitor');
$assert(Url::maintenance_access_allowed(FALSE, ['members'], ['members']), 'Members can access maintenance when their group is allowed');
$assert(Url::maintenance_access_allowed(FALSE, ['members', 'club'], ['club']), 'Custom groups can grant maintenance access');
$assert(!Url::maintenance_access_allowed(FALSE, ['members'], ['partners']), 'Unlisted groups remain blocked by maintenance');
$assert(!Url::maintenance_access_allowed(FALSE, ['members'], ''), 'An empty setting grants no additional access');

$theme = file_get_contents(dirname(__DIR__).'/hiddencms/views/theme/main.tpl.php');
$assert(strpos($theme, '$this->url->maintenance_bypass') !== FALSE, 'Maintenance bypass accounts receive the warning banner');
$assert((bool)preg_match('/if \(\$this->user->admin\).*Open the website/s', $theme), 'Only administrators receive the site-opening action');

echo $checks." checks passed.\n";
