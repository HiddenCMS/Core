<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		$db->execute_checked('ALTER TABLE `pages`
			ADD `parent_id` int(11) unsigned NOT NULL DEFAULT 0 AFTER `outline_id`,
			DROP INDEX `page`,
			ADD UNIQUE KEY `page` (`parent_id`, `name`)');
	}

	public function down($db)
	{
		$db->execute_checked('ALTER TABLE `pages`
			DROP INDEX `page`,
			DROP COLUMN `parent_id`,
			ADD UNIQUE KEY `page` (`name`)');
	}
};
