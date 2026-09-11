<?php

use HB\HiddenCMS\Addons\Migration;

return new class implements Migration
{
	public function up($db)
	{
		foreach (['image', 'hero', 'media_text'] as $widget)
		{
			$db->execute_checked('INSERT IGNORE INTO `addon` (`type_id`, `name`, `data`)
				SELECT `id`, "'.$widget.'", "{\"enabled\":true}"
				FROM `addon_type`
				WHERE `name` = "widget"');
		}
	}

	public function down($db)
	{
		$db->execute_checked('DELETE `addon`
			FROM `addon`
			INNER JOIN `addon_type` ON `addon_type`.`id` = `addon`.`type_id`
			WHERE `addon_type`.`name` = "widget"
			AND `addon`.`name` IN ("image", "hero", "media_text")');
	}
};
