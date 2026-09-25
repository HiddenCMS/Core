<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		$db->execute_checked('UPDATE `settings` SET `value` = "altitude" WHERE `name` = "default_theme" AND `value` = "azuro"');
		$db->execute_checked('UPDATE `outlines` SET `theme` = "altitude" WHERE `theme` = "azuro"');
		$db->execute_checked('DELETE FROM `dispositions` WHERE `theme` = "azuro"');
		$db->execute_checked('DELETE FROM `settings` WHERE `name` LIKE "azuro\\_%"');
		$db->execute_checked('DELETE `addon`
			FROM `addon`
			INNER JOIN `addon_type` ON `addon_type`.`id` = `addon`.`type_id`
			WHERE `addon_type`.`name` = "theme"
			AND `addon`.`name` = "azuro"');
	}

	public function down($db)
	{
		// Azuro is no longer distributed and cannot be restored by a database rollback.
	}
};
