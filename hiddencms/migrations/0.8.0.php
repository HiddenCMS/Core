<?php
use HB\HiddenCMS\Addons\Migration;
return new class implements Migration
{
    public function up($db)
    {
        $db->execute_checked('INSERT IGNORE INTO `addon` (`type_id`, `name`, `data`) SELECT `id`, "language", \'{"enabled":true}\' FROM `addon_type` WHERE `name` = "widget"');
    }
    public function down($db)
    {
        $db->execute_checked('DELETE a FROM `addon` a INNER JOIN `addon_type` t ON t.id = a.type_id WHERE t.name = "widget" AND a.name = "language"');
    }
};
