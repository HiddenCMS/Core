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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `access` (`access_id`, `id`, `module`, `action`) VALUES
(1, 1, 'pages', 'access_page');

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
(2, 1, 'access', '{"enabled":true}'),
(3, 1, 'addons', '{"enabled":true}'),
(4, 1, 'admin', '{"enabled":true}'),
(5, 1, 'comments', '{"enabled":true}'),
(6, 1, 'contact', '{"enabled":true}'),
(7, 1, 'live_editor', '{"enabled":true}'),
(8, 1, 'monitoring', '{"enabled":true}'),
(9, 1, 'news', '{"enabled":true}'),
(10, 1, 'pages', '{"enabled":true}'),
(11, 1, 'search', '{"enabled":true}'),
(12, 1, 'settings', '{"enabled":true}'),
(13, 1, 'statistics', '{"enabled":true}'),
(14, 1, 'user', '{"enabled":true}'),
(15, 2, 'admin', NULL),
(16, 2, 'azuro', NULL),
(17, 3, 'breadcrumb', '{"enabled":true}'),
(18, 3, 'header', '{"enabled":true}'),
(19, 3, 'html', '{"enabled":true}'),
(20, 3, 'module', '{"enabled":true}'),
(21, 3, 'navigation', '{"enabled":true}'),
(22, 3, 'news', '{"enabled":true}'),
(23, 3, 'search', '{"enabled":true}'),
(24, 3, 'user', '{"enabled":true}'),
(25, 4, 'de', '{"order":3,"enabled":true}'),
(26, 4, 'en', '{"order":2,"enabled":true}'),
(27, 4, 'es', '{"order":4,"enabled":true}'),
(28, 4, 'fr', '{"order":1,"enabled":true}'),
(29, 4, 'it', '{"order":5,"enabled":true}'),
(30, 4, 'pt', '{"order":6,"enabled":true}'),
(31, 5, '_battle_net', '{"order":3,"enabled":false,"dev":{"id":"","secret":""},"prod":{"id":"","secret":""}}'),
(32, 5, 'facebook', '{"order":0,"enabled":false,"dev":{"id":"","secret":""},"prod":{"id":"","secret":""}}'),
(33, 5, 'github', '{"order":6,"enabled":false,"dev":{"id":"","secret":""},"prod":{"id":"","secret":""}}'),
(34, 5, 'google', '{"order":2,"enabled":false,"dev":{"id":"","secret":""},"prod":{"id":"","secret":""}}'),
(35, 5, '_linkedin', '{"order":7,"enabled":false,"dev":{"id":"","secret":""},"prod":{"id":"","secret":""}}'),
(36, 5, 'steam', '{"order":4,"enabled":false,"dev":{"key":""},"prod":{"key":""}}'),
(37, 5, '_twitch', '{"order":5,"enabled":false,"dev":{"id":"","secret":""},"prod":{"id":"","secret":""}}'),
(38, 5, '_twitter', '{"order":1,"enabled":false,"dev":{"id":"","secret":""},"prod":{"id":"","secret":""}}'),
(39, 3, 'copyright', '{"enabled":true}'),
(40, 1, 'tools', '{"enabled":true}'),
(41, 3, 'about', '{"enabled":true}'),
(42, 3, 'socials', '{"enabled":true}');

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
(1, 'azuro', '*', 0, '[{"style":"align-items-center","cols":[{"size":"col-9","widgets":[{"id":1,"style":null,"size":"col-7"}]},{"size":null,"widgets":[{"id":2,"style":null,"size":"col-3"}]}]}]'),
(2, 'azuro', '*', 1, '[{"style":"align-items-center","cols":[{"size":null,"widgets":[{"id":27,"style":null,"size":null}]}]}]'),
(3, 'azuro', '*', 2, '[{"style":"align-items-center","cols":[{"size":"col-7","widgets":[{"id":26,"style":null,"size":null}]},{"size":"col-5","widgets":[{"id":5,"style":null,"size":"col-7"}]}]}]'),
(4, 'azuro', '*', 5, '[{"style":null,"cols":[{"size":"col-8","widgets":[{"id":28,"style":null,"size":null}]},{"size":"col-4","widgets":[{"id":8,"style":"card-dark","size":null},{"id":9,"style":null,"size":null},{"id":11,"style":null,"size":null}]}]}]'),
(5, 'azuro', '*', 7, '[{"style":null,"cols":[{"size":null,"widgets":[{"id":29,"style":"card-transparent","size":null}]}]}]'),
(6, 'azuro', '*', 3, '[]'),
(7, 'azuro', '*', 4, '[]'),
(8, 'azuro', '*', 6, '[]'),
(13, 'azuro', 'news/_news/*', 5, '[{"style":null,"cols":[{"size":null,"widgets":[{"id":20,"style":null,"size":null}]}]}]'),
(17, 'azuro', 'user/*', 5, '[{"style":null,"cols":[{"size":"col-12","widgets":[{"id":33,"style":null,"size":null}]}]}]');

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `pages` (`page_id`, `name`, `published`) VALUES
(1, 'accueil', '1');

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

INSERT INTO `pages_lang` (`page_id`, `lang`, `title`, `subtitle`, `content`) VALUES
(1, 'fr', 'Accueil', '', '');

DROP TABLE IF EXISTS `pages_instances`;
CREATE TABLE `pages_instances` (
  `instance_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(11) unsigned NOT NULL,
  `region` varchar(50) NOT NULL DEFAULT 'content',
  `module` varchar(100) NOT NULL,
  `route` varchar(255) NOT NULL,
  `settings` text NOT NULL,
  `position` smallint(5) unsigned NOT NULL DEFAULT '0',
  `enabled` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`instance_id`),
  KEY `page_id` (`page_id`),
  KEY `page_region` (`page_id`,`region`,`enabled`),
  KEY `enabled` (`enabled`),
  CONSTRAINT `pages_instances_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `pages_instances` (`instance_id`, `page_id`, `region`, `module`, `route`, `settings`, `position`, `enabled`) VALUES
(1, 1, 'content', 'news', '', '[]', 0, '1');

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
('contact', '', '', 'noreply@hiddencms.local', 'string'),
('cookie_expire', '', '', '1 hour', 'string'),
('cookie_name', '', '', 'session', 'string'),
('copyright', '', '', 'Copyright {copyright} {year} {name}, tous droits r&eacute;serv&eacute;s &lt;div class=&quot;float-right&quot;&gt;Propuls&eacute; par {hiddencms}&lt;/div&gt;', 'string'),
('default_page', '', '', 'accueil', 'string'),
('default_theme', '', '', 'azuro', 'string'),
('description', '', '', 'HiddenCMS', 'string'),
('favicon', '', '', '0', 'int'),
('http_authentication', '', '', '0', 'bool'),
('http_authentication_name', '', '', '', 'string'),
('humans_txt', '', '', '/* TEAM */\n	HiddenCMS\n	Contact: contact [at] hiddencms.local\n', 'string'),
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
('name', '', '', 'HiddenCMS', 'string'),
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
  `password` varchar(255) NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

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
(1, 'navigation', 'index', NULL, '{"links":[{"title":"Facebook","url":"#"},{"title":"Twitter","url":"#"},{"title":"Origin","url":"#"},{"title":"Steam","url":"#"}]}'),
(2, 'search', 'index', NULL, NULL),
(3, 'header', 'index', NULL, '{"display":"logo","align":"text-center","title":"","description":"","color_title":"#fff","color_description":"#a4b5c5"}'),
(4, 'navigation', 'index', NULL, '{"links":[{"title":"Accueil","url":""},{"title":"Forum","url":"forum"},{"title":"&Eacute;quipes","url":"teams"},{"title":"Matchs","url":"events/matches"},{"title":"Partenaires","url":"partners"},{"title":"Palmar&egrave;s","url":"awards"}]}'),
(5, 'user', 'index_mini', NULL, NULL),
(6, 'module', 'index', NULL, NULL),
(7, 'navigation', 'vertical', NULL, '{"links":[{"title":"Actualit&eacute;s","url":"news"},{"title":"Membres","url":"members"},{"title":"Recrutement","url":"recruits"},{"title":"Photos","url":"gallery"},{"title":"&Eacute;v&eacute;nements","url":"events"},{"title":"Rechercher","url":"search"},{"title":"Contact","url":"contact"}]}'),
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
