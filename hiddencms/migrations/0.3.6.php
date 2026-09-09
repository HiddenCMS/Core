<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		$db->execute_checked('CREATE TABLE IF NOT EXISTS `privacy_retention_run` (
			`id` int unsigned NOT NULL AUTO_INCREMENT,
			`mode` enum("simulation", "purge") NOT NULL,
			`status` enum("completed", "failed") NOT NULL,
			`started_at` datetime NOT NULL,
			`completed_at` datetime NOT NULL,
			`report` text NOT NULL,
			PRIMARY KEY (`id`),
			KEY `completed_at` (`completed_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
		foreach ([
			'privacy_retention_connection_history', 'privacy_retention_sessions',
			'privacy_retention_db_logs', 'privacy_retention_erasure_reports',
			'privacy_retention_backups', 'privacy_retention_log_files',
			'privacy_retention_inactive_accounts'
		] as $name)
		{
			$db->execute_checked('INSERT IGNORE INTO `settings` (`name`, `site`, `lang`, `value`, `type`) VALUES ("'.$name.'", "", "", "0", "int")');
		}
	}

	public function down($db)
	{
		$db->execute_checked('DROP TABLE IF EXISTS `privacy_retention_run`');
		$db->execute_checked('DELETE FROM `settings` WHERE `name` LIKE "privacy_retention_%" AND `site` = "" AND `lang` = ""');
	}
};
