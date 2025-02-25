SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET TIME_ZONE = "+02:00";
SET NAMES utf8;

DROP TABLE IF EXISTS `access`;
CREATE TABLE `access` (
  `access_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id` int(11) unsigned NOT NULL,
  `module` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  PRIMARY KEY (`access_id`),
  UNIQUE KEY `module_id` (`id`,`module`,`action`),
  KEY `module` (`module`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `access_details`;
CREATE TABLE `access_details` (
  `access_id` int(11) unsigned NOT NULL,
  `entity` varchar(100) NOT NULL,
  `type` enum('group','user') NOT NULL DEFAULT 'group',
  `authorized` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`access_id`,`entity`,`type`),
  CONSTRAINT `access_details_ibfk_1` FOREIGN KEY (`access_id`) REFERENCES `access` (`access_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `addon`;
CREATE TABLE `addon` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `data` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`type_id`),
  KEY `type_id` (`type_id`),
  CONSTRAINT `addon_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `addon_type` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8;


INSERT INTO `addon` (`id`, `type_id`, `name`, `data`) VALUES
(1, NULL, 'authenticator', NULL),
(2, 1, 'access', 'a:1:{s:7:\"enabled\";b:1;}'),
(3, 1, 'addons', 'a:1:{s:7:\"enabled\";b:1;}'),
(4, 1, 'admin', 'a:1:{s:7:\"enabled\";b:1;}'),
(5, 1, 'comments', 'a:1:{s:7:\"enabled\";b:1;}'),
(6, 1, 'contact', 'a:1:{s:7:\"enabled\";b:1;}'),
(7, 1, 'live_editor', 'a:1:{s:7:\"enabled\";b:1;}'),
(8, 1, 'monitoring', 'a:1:{s:7:\"enabled\";b:1;}'),
(9, 1, 'news', 'a:1:{s:7:\"enabled\";b:1;}'),
(10, 1, 'pages', 'a:1:{s:7:\"enabled\";b:1;}'),
(11, 1, 'search', 'a:1:{s:7:\"enabled\";b:1;}'),
(12, 1, 'settings', 'a:1:{s:7:\"enabled\";b:1;}'),
(13, 1, 'statistics', 'a:1:{s:7:\"enabled\";b:1;}'),
(14, 1, 'user', 'a:1:{s:7:\"enabled\";b:1;}'),
(15, 2, 'admin', NULL),
(16, 2, 'azuro', NULL),
(17, 3, 'breadcrumb', 'a:1:{s:7:\"enabled\";b:1;}'),
(18, 3, 'header', 'a:1:{s:7:\"enabled\";b:1;}'),
(19, 3, 'html', 'a:1:{s:7:\"enabled\";b:1;}'),
(20, 3, 'module', 'a:1:{s:7:\"enabled\";b:1;}'),
(21, 3, 'navigation', 'a:1:{s:7:\"enabled\";b:1;}'),
(22, 3, 'news', 'a:1:{s:7:\"enabled\";b:1;}'),
(23, 3, 'search', 'a:1:{s:7:\"enabled\";b:1;}'),
(24, 3, 'user', 'a:1:{s:7:\"enabled\";b:1;}'),
(25, 4, 'de', 'a:2:{s:5:\"order\";i:3;s:7:\"enabled\";b:1;}'),
(26, 4, 'en', 'a:2:{s:5:\"order\";i:2;s:7:\"enabled\";b:1;}'),
(27, 4, 'es', 'a:2:{s:5:\"order\";i:4;s:7:\"enabled\";b:1;}'),
(28, 4, 'fr', 'a:2:{s:5:\"order\";i:1;s:7:\"enabled\";b:1;}'),
(29, 4, 'it', 'a:2:{s:5:\"order\";i:5;s:7:\"enabled\";b:1;}'),
(30, 4, 'pt', 'a:2:{s:5:\"order\";i:6;s:7:\"enabled\";b:1;}'),
(31, 5, '_battle_net', 'a:4:{s:5:\"order\";i:3;s:7:\"enabled\";b:0;s:3:\"dev\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}s:4:\"prod\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}}'),
(32, 5, 'facebook', 'a:4:{s:5:\"order\";i:0;s:7:\"enabled\";b:0;s:3:\"dev\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}s:4:\"prod\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}}'),
(33, 5, 'github', 'a:4:{s:5:\"order\";i:6;s:7:\"enabled\";b:0;s:3:\"dev\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}s:4:\"prod\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}}'),
(34, 5, 'google', 'a:4:{s:5:\"order\";i:2;s:7:\"enabled\";b:0;s:3:\"dev\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}s:4:\"prod\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}}'),
(35, 5, '_linkedin', 'a:4:{s:5:\"order\";i:7;s:7:\"enabled\";b:0;s:3:\"dev\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}s:4:\"prod\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}}'),
(36, 5, 'steam', 'a:4:{s:5:\"order\";i:4;s:7:\"enabled\";b:0;s:3:\"dev\";a:1:{s:3:\"key\";s:0:\"\";}s:4:\"prod\";a:1:{s:3:\"key\";s:0:\"\";}}'),
(37, 5, '_twitch', 'a:4:{s:5:\"order\";i:5;s:7:\"enabled\";b:0;s:3:\"dev\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}s:4:\"prod\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}}'),
(38, 5, '_twitter', 'a:4:{s:5:\"order\";i:1;s:7:\"enabled\";b:0;s:3:\"dev\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}s:4:\"prod\";a:2:{s:2:\"id\";s:0:\"\";s:6:\"secret\";s:0:\"\";}}'),
(39, 3, 'copyright', 'a:1:{s:7:\"enabled\";b:1;}'),
(40, 1, 'tools', 'a:1:{s:7:\"enabled\";b:1;}'),
(41, 3, 'about', 'a:1:{s:7:\"enabled\";b:1;}'),
(42, 3, 'socials', 'a:1:{s:7:\"enabled\";b:1;}');

DROP TABLE IF EXISTS `addon_type`;
CREATE TABLE `addon_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO `addon_type` (`id`, `name`) VALUES
(1, 'module'),
(2, 'theme'),
(3, 'widget'),
(4, 'language'),
(5, 'authenticator');

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `module_id` int(11) unsigned NOT NULL,
  `module` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`),
  KEY `module` (`module`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `dispositions`;
CREATE TABLE `dispositions` (
  `disposition_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `theme` varchar(100) NOT NULL,
  `page` varchar(100) NOT NULL,
  `zone` int(11) unsigned NOT NULL,
  `disposition` text NOT NULL,
  PRIMARY KEY (`disposition_id`),
  UNIQUE KEY `theme` (`theme`,`page`,`zone`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

INSERT INTO `dispositions` (`disposition_id`, `theme`, `page`, `zone`, `disposition`) VALUES
(1, 'azuro', '*', 0, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Row\":2:{s:9:\"\0*\0_array\";a:2:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:1;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";s:5:\"col-7\";}}s:8:\"\0*\0_size\";s:5:\"col-9\";}i:1;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:2;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";s:5:\"col-3\";}}s:8:\"\0*\0_size\";N;}}s:9:\"\0*\0_style\";s:18:\"align-items-center\";}}}'),
(2, 'azuro', '*', 1, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Row\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:27;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";N;}}s:8:\"\0*\0_size\";N;}}s:9:\"\0*\0_style\";s:18:\"align-items-center\";}}}'),
(3, 'azuro', '*', 2, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Row\":2:{s:9:\"\0*\0_array\";a:2:{i:1;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:26;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";N;}}s:8:\"\0*\0_size\";s:5:\"col-7\";}i:0;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:5;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";s:5:\"col-7\";}}s:8:\"\0*\0_size\";s:5:\"col-5\";}}s:9:\"\0*\0_style\";s:18:\"align-items-center\";}}}'),
(4, 'azuro', '*', 5, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Row\":2:{s:9:\"\0*\0_array\";a:2:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:28;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";N;}}s:8:\"\0*\0_size\";s:5:\"col-8\";}i:1;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:3:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:8;s:9:\"\0*\0_style\";s:9:\"card-dark\";s:8:\"\0*\0_size\";N;}i:1;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:9;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";N;}i:3;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:11;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";N;}}s:8:\"\0*\0_size\";s:5:\"col-4\";}}s:9:\"\0*\0_style\";N;}}}'),
(5, 'azuro', '*', 7, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Row\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:29;s:9:\"\0*\0_style\";s:16:\"card-transparent\";s:8:\"\0*\0_size\";N;}}s:8:\"\0*\0_size\";N;}}s:9:\"\0*\0_style\";N;}}}'),
(6, 'azuro', '*', 3, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:0:{}}'),
(7, 'azuro', '*', 4, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:0:{}}'),
(8, 'azuro', '*', 6, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:0:{}}'),
(13, 'azuro', 'news/_news/*', 5, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Row\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:20;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";N;}}s:8:\"\0*\0_size\";N;}}s:9:\"\0*\0_style\";N;}}}'),
(17, 'azuro', 'user/*', 5, 'O:27:\"NF\\NeoFrag\\Libraries\\Array_\":1:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Row\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:27:\"NF\\NeoFrag\\Displayables\\Col\":2:{s:9:\"\0*\0_array\";a:1:{i:0;O:30:\"NF\\NeoFrag\\Displayables\\Widget\":3:{s:10:\"\0*\0_widget\";i:33;s:9:\"\0*\0_style\";N;s:8:\"\0*\0_size\";N;}}s:8:\"\0*\0_size\";s:6:\"col-12\";}}s:9:\"\0*\0_style\";N;}}}');

DROP TABLE IF EXISTS `file`;
CREATE TABLE `file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `path` varchar(100) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `file_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

INSERT INTO `file` (`id`, `user_id`, `name`, `path`, `date`) VALUES
(1, 1, 'Sans-titre-2.jpg', './upload/news/categories/ubfuejdfooirqya0pyltfeklja4ew4sn.jpg', '2015-05-30 00:34:16'),
(2, 1, 'logo.png', 'upload/partners/zwvmsjijfljaka4rdblgvlype1lnbwaw.png', '2016-05-07 18:51:53'),
(3, 1, 'logo_black.png', 'upload/partners/y4ofwq2ekppwnfpmnrmnafeivszlg5bd.png', '2016-05-07 18:51:53');

DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `color` varchar(20) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `hidden` enum('0','1') NOT NULL DEFAULT '0',
  `auto` enum('0','1') NOT NULL DEFAULT '0',
  `order` smallint(6) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `groups_lang`;
CREATE TABLE `groups_lang` (
  `group_id` int(11) unsigned NOT NULL,
  `lang` varchar(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`group_id`,`lang`),
  KEY `lang` (`lang`),
  CONSTRAINT `groups_lang_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `i18n`;
CREATE TABLE `i18n` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lang_id` int(10) unsigned NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `model_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lang_id` (`lang_id`,`model`,`model_id`,`name`) USING BTREE,
  KEY `lang_id_2` (`lang_id`),
  KEY `model` (`model`),
  KEY `model_id` (`model_id`),
  KEY `name` (`name`),
  CONSTRAINT `i18n_ibfk_1` FOREIGN KEY (`lang_id`) REFERENCES `addon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `log_db`;
CREATE TABLE `log_db` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` enum('0','1','2') NOT NULL,
  `model` varchar(100) NOT NULL,
  `primaries` varchar(100) DEFAULT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `log_i18n`;
CREATE TABLE `log_i18n` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language` char(2) NOT NULL,
  `key` char(32) NOT NULL,
  `locale` text NOT NULL,
  `file` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `language` (`language`,`key`,`file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `published` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `page` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `pages_lang`;
CREATE TABLE `pages_lang` (
  `page_id` int(11) unsigned NOT NULL,
  `lang` varchar(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  `subtitle` varchar(100) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`page_id`,`lang`),
  KEY `lang` (`lang`),
  CONSTRAINT `pages_lang_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `search_keywords`;
CREATE TABLE `search_keywords` (
  `keyword` varchar(100) NOT NULL,
  `count` int(11) unsigned NOT NULL,
  PRIMARY KEY (`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session` (
  `id` varchar(32) NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `remember` enum('0','1') NOT NULL DEFAULT '0',
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `session_history`;
CREATE TABLE `session_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `ip_address` varchar(39) NOT NULL,
  `host_name` varchar(100) NOT NULL,
  `referer` varchar(100) NOT NULL,
  `user_agent` varchar(250) NOT NULL,
  `auth` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `session_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `name` varchar(100) NOT NULL,
  `site` varchar(100) NOT NULL DEFAULT '',
  `lang` varchar(5) NOT NULL DEFAULT '',
  `value` text,
  `type` enum('string','bool','int','list','array','float') NOT NULL DEFAULT 'string',
  PRIMARY KEY (`name`,`site`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `settings` (`name`, `site`, `lang`, `value`, `type`) VALUES
('azuro_background', '', '', '0', 'int'),
('azuro_background_attachment', '', '', 'scroll', 'string'),
('azuro_background_color', '', '', '#343a40', 'string'),
('azuro_background_position', '', '', 'center top', 'string'),
('azuro_background_repeat', '', '', 'no-repeat', 'string'),
('azuro_primary_color', '', '', '#00d7b3', 'string'),
('azuro_secondary_color', '', '', '#00c7e4', 'string'),
('azuro_text_color', '', '', '#212529', 'string'),
('news_per_page', '', '', '5', 'int'),
('analytics', '', '', '', 'string'),
('captcha_private_key', '', '', '', 'string'),
('captcha_public_key', '', '', '', 'string'),
('contact', '', '', 'noreply@hidden.com', 'string'),
('cookie_expire', '', '', '1 hour', 'string'),
('cookie_name', '', '', 'session', 'string'),
('copyright', '', '', 'Copyright {copyright} {year} {name}, tous droits r&eacute;serv&eacute;s &lt;div class=&quot;float-right&quot;&gt;Propuls&eacute; par {hidden}&lt;/div&gt;', 'string'),
('default_page', '', '', 'news', 'string'),
('default_theme', '', '', 'azuro', 'string'),
('description', '', '', 'ALPHA 0.2.3', 'string'),
('favicon', '', '', '0', 'int'),
('http_authentication', '', '', '0', 'bool'),
('http_authentication_name', '', '', '', 'string'),
('humans_txt', '', '', '/* TEAM */\n	Hidden CMS for gamers\n	Contact: contact [at] hidden.com\n	Twitter: @HiddenCMS\n', 'string'),
('maintenance', '', '', '0', 'bool'),
('maintenance_background', '', '', '0', 'int'),
('maintenance_background_color', '', '', '', 'string'),
('maintenance_background_position', '', '', '', 'string'),
('maintenance_background_repeat', '', '', '', 'string'),
('maintenance_content', '', '', '', 'string'),
('maintenance_logo', '', '', '0', 'int'),
('maintenance_opening', '', '', '', 'string'),
('maintenance_text_color', '', '', '', 'string'),
('maintenance_title', '', '', '', 'string'),
('monitoring_last_check', '', '', '0', 'int'),
('name', '', '', 'Hidden', 'string'),
('registration_charte', '', '', '', 'string'),
('registration_status', '', '', '1', 'bool'),
('robots_txt', '', '', 'User-agent: *\r\nDisallow:', 'string'),
('social_behance', '', '', '', 'string'),
('social_deviantart', '', '', '', 'string'),
('social_dribble', '', '', '', 'string'),
('social_facebook', '', '', '', 'string'),
('social_flickr', '', '', '', 'string'),
('social_github', '', '', '', 'string'),
('social_google', '', '', '', 'string'),
('social_instagram', '', '', '', 'string'),
('social_steam', '', '', '', 'string'),
('social_twitch', '', '', '', 'string'),
('social_twitter', '', '', '', 'string'),
('social_youtube', '', '', '', 'string'),
('team_biographie', '', '', '', 'string'),
('team_creation', '', '', '', 'string'),
('team_logo', '', '', '0', 'int'),
('team_name', '', '', '', 'string'),
('team_type', '', '', '', 'string'),
('theme_color', '', '', '#2b373a', 'string'),
('update_callback', '', '', '', 'string'),
('version_css', '', '', '1593080828', 'int'),
('welcome', '', '', '0', 'bool'),
('welcome_content', '', '', '', 'string'),
('welcome_title', '', '', '', 'string'),
('welcome_user_id', '', '', '0', 'int');

DROP TABLE IF EXISTS `statistics`;
CREATE TABLE `statistics` (
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `statistics` (`name`, `value`) VALUES
('sessions_max_simultaneous', '0');

DROP TABLE IF EXISTS `tracking`;
CREATE TABLE `tracking` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `model` varchar(100) NOT NULL,
  `model_id` int(10) unsigned DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`model`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `news_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `image_id` int(11) unsigned DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published` enum('0','1') NOT NULL DEFAULT '0',
  `views` int(11) unsigned NOT NULL DEFAULT '0',
  `vote` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`news_id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`),
  KEY `image_id` (`image_id`),
  CONSTRAINT `news_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `news_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `news_ibfk_4` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `news_categories`;
CREATE TABLE `news_categories` (
  `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `image_id` int(11) unsigned DEFAULT NULL,
  `icon_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `image_id` (`image_id`),
  KEY `icon_id` (`icon_id`),
  CONSTRAINT `news_categories_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `news_categories_ibfk_2` FOREIGN KEY (`icon_id`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `news_categories` (`category_id`, `image_id`, `icon_id`, `name`) VALUES
(1, 1, NULL, 'general');

DROP TABLE IF EXISTS `news_categories_lang`;
CREATE TABLE `news_categories_lang` (
  `category_id` int(11) unsigned NOT NULL,
  `lang` varchar(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`,`lang`),
  KEY `lang` (`lang`),
  CONSTRAINT `news_categories_lang_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `news_categories_lang` (`category_id`, `lang`, `title`) VALUES
(1, 'fr', 'G&eacute;n&eacute;ral');

DROP TABLE IF EXISTS `news_lang`;
CREATE TABLE `news_lang` (
  `news_id` int(11) unsigned NOT NULL,
  `lang` varchar(5) NOT NULL,
  `title` varchar(100) NOT NULL,
  `introduction` text NOT NULL,
  `content` text NOT NULL,
  `tags` text NOT NULL,
  PRIMARY KEY (`news_id`,`lang`),
  KEY `lang` (`lang`),
  CONSTRAINT `news_lang_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(34) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity_date` timestamp NULL DEFAULT NULL,
  `admin` enum('0','1') NOT NULL DEFAULT '0',
  `language` int(10) unsigned DEFAULT NULL,
  `data` text NOT NULL,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `language` (`language`),
  KEY `deleted` (`deleted`),
  CONSTRAINT `user_ibfk_1` FOREIGN KEY (`language`) REFERENCES `addon` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `user` (`id`, `username`, `password`, `salt`, `email`, `registration_date`, `last_activity_date`, `admin`, `language`, `data`, `deleted`) VALUES
(1, 'admin', '$H$92EwygSmbdXunbIvoo/V91MWcnHqzX/', '', 'noreply@hiddenblob.com', CURRENT_TIMESTAMP, NULL, '1', NULL, '', '0');

DROP TABLE IF EXISTS `user_auth`;
CREATE TABLE `user_auth` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `authenticator_id` int(11) unsigned NOT NULL,
  `key` varchar(100) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `avatar` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`authenticator_id`,`key`),
  KEY `authenticator_id` (`authenticator_id`),
  CONSTRAINT `user_auth_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_auth_ibfk_2` FOREIGN KEY (`authenticator_id`) REFERENCES `addon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE `user_profile` (
  `id` int(11) unsigned NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `avatar` int(11) unsigned DEFAULT NULL,
  `cover` int(11) unsigned DEFAULT NULL,
  `signature` text NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `sex` enum('male','female') DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `location` varchar(100) NOT NULL,
  `quote` varchar(100) NOT NULL,
  `website` varchar(100) NOT NULL,
  `linkedin` varchar(100) NOT NULL,
  `github` varchar(100) NOT NULL,
  `instagram` varchar(100) NOT NULL,
  `twitch` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `avatar` (`avatar`),
  KEY `cover` (`cover`),
  CONSTRAINT `user_profile_ibfk_2` FOREIGN KEY (`avatar`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `user_profile_ibfk_3` FOREIGN KEY (`id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `user_profile_ibfk_4` FOREIGN KEY (`cover`) REFERENCES `files` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `user_token`;
CREATE TABLE `user_token` (
  `id` varchar(32) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users_groups`;
CREATE TABLE `users_groups` (
  `user_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `users_groups_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users_messages`;
CREATE TABLE `users_messages` (
  `message_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `reply_id` int(11) unsigned DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `last_reply_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`message_id`),
  KEY `reply_id` (`reply_id`),
  KEY `last_reply_id` (`last_reply_id`),
  CONSTRAINT `users_messages_ibfk_1` FOREIGN KEY (`reply_id`) REFERENCES `users_messages_replies` (`reply_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_messages_ibfk_2` FOREIGN KEY (`last_reply_id`) REFERENCES `users_messages_replies` (`reply_id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users_messages_recipients`;
CREATE TABLE `users_messages_recipients` (
  `user_id` int(11) unsigned NOT NULL,
  `message_id` int(11) unsigned NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `deleted` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`message_id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `users_messages_recipients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_messages_recipients_ibfk_2` FOREIGN KEY (`message_id`) REFERENCES `users_messages` (`message_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `users_messages_replies`;
CREATE TABLE `users_messages_replies` (
  `reply_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `message` text NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reply_id`),
  KEY `message_id` (`message_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `users_messages_replies_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `users_messages` (`message_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `users_messages_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `widgets`;
CREATE TABLE `widgets` (
  `widget_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `widget` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `settings` text,
  PRIMARY KEY (`widget_id`),
  KEY `widget_name` (`widget`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

INSERT INTO `widgets` (`widget_id`, `widget`, `type`, `title`, `settings`) VALUES
(1, 'navigation', 'index', NULL, 'a:1:{s:5:\"links\";a:4:{i:0;a:2:{s:5:\"title\";s:8:\"Facebook\";s:3:\"url\";s:1:\"#\";}i:1;a:2:{s:5:\"title\";s:7:\"Twitter\";s:3:\"url\";s:1:\"#\";}i:2;a:2:{s:5:\"title\";s:6:\"Origin\";s:3:\"url\";s:1:\"#\";}i:3;a:2:{s:5:\"title\";s:5:\"Steam\";s:3:\"url\";s:1:\"#\";}}}'),
(2, 'search', 'index', NULL, NULL),
(3, 'header', 'index', NULL, 'a:6:{s:7:\"display\";s:4:\"logo\";s:5:\"align\";s:11:\"text-center\";s:5:\"title\";s:0:\"\";s:11:\"description\";s:0:\"\";s:11:\"color_title\";s:4:\"#fff\";s:17:\"color_description\";s:7:\"#a4b5c5\";}'),
(4, 'navigation', 'index', NULL, 'a:1:{s:5:\"links\";a:6:{i:0;a:2:{s:5:\"title\";s:7:\"Accueil\";s:3:\"url\";s:0:\"\";}i:1;a:2:{s:5:\"title\";s:5:\"Forum\";s:3:\"url\";s:5:\"forum\";}i:2;a:2:{s:5:\"title\";s:14:\"&Eacute;quipes\";s:3:\"url\";s:5:\"teams\";}i:3;a:2:{s:5:\"title\";s:6:\"Matchs\";s:3:\"url\";s:14:\"events/matches\";}i:4;a:2:{s:5:\"title\";s:11:\"Partenaires\";s:3:\"url\";s:8:\"partners\";}i:5;a:2:{s:5:\"title\";s:15:\"Palmar&egrave;s\";s:3:\"url\";s:6:\"awards\";}}}'),
(5, 'user', 'index_mini', NULL, NULL),
(6, 'module', 'index', NULL, NULL),
(7, 'navigation', 'vertical', NULL, 'a:1:{s:5:\"links\";a:7:{i:0;a:2:{s:5:\"title\";s:17:\"Actualit&eacute;s\";s:3:\"url\";s:4:\"news\";}i:1;a:2:{s:5:\"title\";s:7:\"Membres\";s:3:\"url\";s:7:\"members\";}i:2;a:2:{s:5:\"title\";s:11:\"Recrutement\";s:3:\"url\";s:8:\"recruits\";}i:3;a:2:{s:5:\"title\";s:6:\"Photos\";s:3:\"url\";s:7:\"gallery\";}i:4;a:2:{s:5:\"title\";s:24:\"&Eacute;v&eacute;nements\";s:3:\"url\";s:6:\"events\";}i:5;a:2:{s:5:\"title\";s:10:\"Rechercher\";s:3:\"url\";s:6:\"search\";}i:6;a:2:{s:5:\"title\";s:7:\"Contact\";s:3:\"url\";s:7:\"contact\";}}}'),
(8, 'user', 'index', NULL, NULL),
(9, 'news', 'categories', NULL, NULL),
(10, 'copyright', 'index', NULL, NULL),
(11, 'news', 'index', NULL, NULL),
(12, 'module', 'index', NULL, NULL),
(13, 'module', 'index', NULL, NULL),
(14, 'module', 'index', NULL, NULL),
(15, 'module', 'index', NULL, NULL),
(16, 'module', 'index', NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;