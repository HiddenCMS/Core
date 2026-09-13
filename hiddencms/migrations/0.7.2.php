<?php
use HB\HiddenCMS\Addons\Migration;
return new class implements Migration {
    public function up($db) {
        $db->execute_checked('CREATE TABLE IF NOT EXISTS statistics_daily (
            day DATE NOT NULL, path VARCHAR(255) CHARACTER SET ascii NOT NULL, views INT UNSIGNED NOT NULL DEFAULT 0,
            visits INT UNSIGNED NOT NULL DEFAULT 0, PRIMARY KEY(day, path)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        $db->execute_checked('CREATE TABLE IF NOT EXISTS statistics_seen (
            day DATE NOT NULL, visitor CHAR(64) CHARACTER SET ascii NOT NULL, path VARCHAR(255) CHARACTER SET ascii NOT NULL,
            PRIMARY KEY(day, visitor, path)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }
    public function down($db) {
        $db->execute_checked('DROP TABLE IF EXISTS statistics_seen');
        $db->execute_checked('DROP TABLE IF EXISTS statistics_daily');
    }
};
