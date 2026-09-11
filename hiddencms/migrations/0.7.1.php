<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		$db->execute_checked('ALTER TABLE `outlines`
			ADD `breadcrumb` enum("0", "1") NOT NULL DEFAULT "1" AFTER `base`');
	}

	public function down($db)
	{
		$db->execute_checked('ALTER TABLE `outlines`
			DROP COLUMN `breadcrumb`');
	}
};
