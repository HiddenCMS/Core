<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		$db->execute_checked('CREATE TABLE IF NOT EXISTS `user_field` (
			`id` int unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(60) NOT NULL,
			`label` varchar(100) NOT NULL,
			`type` varchar(16) NOT NULL,
			`options` text NOT NULL,
			`required` tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`), UNIQUE KEY (`name`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		$db->execute_checked('CREATE TABLE IF NOT EXISTS `user_field_value` (
			`user_id` int unsigned NOT NULL,
			`field_id` int unsigned NOT NULL,
			`value` text NOT NULL,
			`login_value` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
			PRIMARY KEY (`user_id`, `field_id`),
			UNIQUE KEY `user_field_login` (`field_id`, `login_value`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		$db->execute_checked('CREATE TABLE IF NOT EXISTS `user_login` (
			`id` tinyint unsigned NOT NULL,
			`identifier` varchar(80) NOT NULL DEFAULT "username",
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		$db->execute_checked('INSERT IGNORE INTO `user_login` (`id`, `identifier`) VALUES (1, "username")');
	}

	public function down($db)
	{
		$db->execute_checked('DROP TABLE IF EXISTS `user_field_value`, `user_field`, `user_login`');
	}
};
