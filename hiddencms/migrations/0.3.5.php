<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		$db->execute_checked('CREATE TABLE IF NOT EXISTS `user_erasure_request` (
			`user_id` int(11) unsigned NOT NULL,
			`requested_at` datetime NOT NULL,
			`execute_after` datetime NOT NULL,
			`completed_at` datetime DEFAULT NULL,
			`result` text DEFAULT NULL,
			PRIMARY KEY (`user_id`),
			KEY `execute_after` (`execute_after`, `completed_at`),
			CONSTRAINT `user_erasure_request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		$db->execute_checked('INSERT IGNORE INTO `settings` (`name`, `site`, `lang`, `value`, `type`) VALUES ("privacy_erasure_delay", "", "", "30", "int")');
	}

	public function down($db)
	{
		$db->execute_checked('DROP TABLE IF EXISTS `user_erasure_request`');
		$db->execute_checked('DELETE FROM `settings` WHERE `name` = "privacy_erasure_delay" AND `site` = "" AND `lang` = ""');
	}
};
