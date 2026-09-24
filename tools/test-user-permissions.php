<?php

if (PHP_SAPI !== 'cli' || !in_array('--isolated-database', $argv, TRUE))
{
	fwrite(STDERR, "Usage: php tools/test-user-permissions.php --isolated-database\nRequires CREATE/DROP DATABASE privileges on a development server.\n");
	exit(1);
}

chdir(dirname(__DIR__));
define('HIDDENCMS_CLI', TRUE);
$_SERVER['REQUEST_METHOD'] = 'GET';
require 'index.php';

$db        = HB()->db;
$module    = HB()->module('user');
$original  = $db->query('SELECT DATABASE()')->row();
$temporary = 'hb_test_user_permissions_'.bin2hex(random_bytes(6));
$quote     = function($name){ return '`'.str_replace('`', '``', $name).'`'; };
$checks    = 0;
$assert    = function($condition, $message) use (&$checks){
	if (!$condition) throw new RuntimeException($message);
	$checks++;
	echo 'PASS '.$message.PHP_EOL;
};
$grant = function($action, $user_id, $module_name = 'user') use ($db){
	$access_id = $db->insert('access', ['module' => $module_name, 'action' => $action, 'id' => 0]);
	$db->insert('access_details', [
		'access_id'  => $access_id,
		'entity'     => $user_id,
		'type'       => 'user',
		'authorized' => TRUE
	]);
};

$db->execute_checked('CREATE DATABASE '.$quote($temporary).' CHARACTER SET utf8mb4');

try
{
	foreach ($db->query('SHOW TABLES')->get() as $table)
	{
		$db->execute_checked('CREATE TABLE '.$quote($temporary).'.'.$quote($table).' LIKE '.$quote($original).'.'.$quote($table));
	}

	$db->execute_script('USE '.$quote($temporary));
	$statements = new ReflectionProperty($db->driver(), 'stmt');
	$statements->setAccessible(TRUE);
	$statements->setValue($db->driver(), []);

	$permission_keys = [];
	foreach ($module->permissions()['default']['access'] as $section)
	{
		$permission_keys = array_merge($permission_keys, array_keys($section['access']));
	}

	$assert($permission_keys === [
		'view_users',
		'create_users',
		'edit_users',
		'delete_users',
		'assign_user_groups',
		'manage_groups',
		'manage_sessions'
	], 'User administration exposes the expected granular permissions');

	$next_user_id = 10000;
	$create_user = function($username, $admin = FALSE) use ($module, &$next_user_id){
		return $module->model2('user')
			->set('id', $next_user_id++)
			->set('username', $username)
			->set('email', $username.'@example.test')
			->set('password', password_hash('PermissionTest!72', PASSWORD_DEFAULT))
			->set('registration_date', date('Y-m-d H:i:s'))
			->set('admin', $admin)
			->set('data', [])
			->set('deleted', FALSE)
			->create();
	};

	$delegate = $create_user('permission-manager');
	$target   = $create_user('regular-target');
	$admin    = $create_user('admin-target', TRUE);

	$assert(!$delegate->admin, 'A delegated account is created as a regular user');

	$grant('create_users', $delegate->id);
	$grant('assign_user_groups', $delegate->id);
	HB()->access->reload();

	$assert(HB()->access('user', 'create_users', 0, NULL, $delegate->id), 'The delegated account receives the user creation permission');
	$assert(HB()->access('user', 'assign_user_groups', 0, NULL, $delegate->id), 'The delegated account receives the group assignment permission');
	$assert(!HB()->access('user', 'manage_groups', 0, NULL, $delegate->id), 'User creation does not implicitly grant group management');
	$assert(!HB()->access('user', 'delete_users', 0, NULL, $delegate->id), 'User creation does not implicitly grant user deletion');

	$statistics_permissions = HB()->module('statistics')->permissions()['default']['access'][0]['access'];
	$assert(isset($statistics_permissions['view_statistics']), 'Statistics exposes a delegated viewing permission');
	$grant('view_statistics', $delegate->id, 'statistics');
	HB()->access->reload();
	$assert(HB()->access('statistics', 'view_statistics', 0, NULL, $delegate->id), 'A delegated account can receive statistics access');

	$current_user = HB()->user;
	$previous_id = $current_user->id;
	$previous_admin = $current_user->admin;
	$current_user->set('id', $delegate->id)->set('admin', FALSE);

	try
	{
		$assert(HB()->module('statistics')->is_authorized(), 'Statistics module accepts a delegated viewer');

		$group_id = $db->insert('groups', [
			'name'   => 'editors',
			'color'  => 'info',
			'icon'   => 'fas fa-pen',
			'hidden' => FALSE,
			'auto'   => FALSE
		]);
		$db->insert('users_groups', [
			'user_id'  => $delegate->id,
			'group_id' => $group_id
		]);

		$maintenance_groups = new ReflectionMethod(HB()->url, 'maintenance_user_groups');
		$maintenance_groups->setAccessible(TRUE);
		$resolved_groups = $maintenance_groups->invoke(HB()->url);

		$assert(in_array('members', $resolved_groups, TRUE), 'Maintenance resolves the implicit member group during session initialization');
		$assert(in_array((string)$group_id, $resolved_groups, TRUE), 'Maintenance resolves persisted custom groups before the group service is loaded');
		$assert(HB()->url::maintenance_access_allowed(FALSE, $resolved_groups, [(string)$group_id]), 'A persisted custom group grants maintenance access');

		$update = $target->action('update');
		$check = new ReflectionMethod($update, 'check');
		$check->setAccessible(TRUE);
		$assert($check->invoke($update, $target), 'A delegated manager can assign groups to a regular user');
		$assert(!$check->invoke($update, $admin), 'A delegated manager cannot edit an administrator');
		$assert(strpos((string)$update, 'value="admins"') === FALSE, 'The administrator group is hidden from delegated group assignment');
		$assert(!HB()->module('access')->is_authorized(), 'A delegated manager cannot grant permissions');
	}
	finally
	{
		$current_user->set('id', $previous_id)->set('admin', $previous_admin);
	}

	echo $checks." checks passed.\n";
}
finally
{
	$db->execute_script('USE '.$quote($original));
	$statements = new ReflectionProperty($db->driver(), 'stmt');
	$statements->setAccessible(TRUE);
	$statements->setValue($db->driver(), []);
	$db->execute_checked('DROP DATABASE IF EXISTS '.$quote($temporary));
}
