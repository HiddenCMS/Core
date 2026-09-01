<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		$db->execute_checked('CREATE TABLE IF NOT EXISTS `core_migrations` (
			`migration` varchar(190) NOT NULL,
			`version` varchar(50) NOT NULL,
			`batch` int unsigned NOT NULL,
			`applied_at` datetime NOT NULL,
			PRIMARY KEY (`migration`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
	}

	public function down($db)
	{
		// This bootstrap migration intentionally keeps its own journal table.
	}
};
