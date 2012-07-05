-- Adminer 3.4.0-dev MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `cache_dirs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `content` (
  `node_id` int(11) NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lg` char(2) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cache_title` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_node_lg` (`node_id`,`lg`),
  KEY `active_index` (`active`),
  KEY `node_id` (`node_id`),
  FULLTEXT KEY `title` (`cache_title`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `content_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL,
  `value` varchar(1) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contentid` (`content_id`),
  KEY `weight_index` (`weight`),
  KEY `name_index` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `content_field_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_field_id` int(11) NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `content_field_id` (`content_field_id`),
  KEY `value_index` (`value`(8)),
  FULLTEXT KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `content_mapping` (
  `mapping_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lg` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `unique_node_lg` (`mapping_id`,`lg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `content_mapping_field` (
  `content_mapping_id` int(11) NOT NULL,
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `element` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_change` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unique_node_lg` (`content_mapping_id`,`element`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `content_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lg` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `query` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `query` (`query`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `cron` (
  `type` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `start_run` bigint(20) NOT NULL,
  `end_run` bigint(20) NOT NULL,
  PRIMARY KEY (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `directory_properties` (
  `directory_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `resize_type` enum('w','m','h') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'w',
  `max_size` int(5) DEFAULT NULL,
  `th_size` int(4) DEFAULT NULL,
  `alt_size` int(4) DEFAULT NULL,
  PRIMARY KEY (`directory_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;











CREATE TABLE `fe_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `firstname` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `firma` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `zip` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fe_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fe_groups2user` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fe_restrictions` (
  `node_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`node_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fe_user_address` (
  `fe_user_id` int(11) NOT NULL,
  `fe_address_id` int(11) NOT NULL,
  PRIMARY KEY (`fe_user_id`,`fe_address_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fe_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fieldgroup` (
  `fieldgroup_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `visibility_level` smallint(6) NOT NULL DEFAULT '2',
  `weight` int(11) NOT NULL,
  PRIMARY KEY (`fieldgroup_id`),
  KEY `visibility_level` (`visibility_level`),
  KEY `weight` (`weight`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fieldgroup_entry` (
  `fieldgroup_entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldgroup_id` int(11) NOT NULL,
  `selector` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`fieldgroup_entry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fieldgroup_selection` (
  `fieldgroup_selection_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `is_standard` tinyint(1) NOT NULL,
  PRIMARY KEY (`fieldgroup_selection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `fieldgroup_selection_entry` (
  `fieldgroup_selection_entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldgroup_selection_id` int(11) NOT NULL,
  `fieldgroup_id` int(11) NOT NULL,
  PRIMARY KEY (`fieldgroup_selection_entry_id`),
  UNIQUE KEY `fieldgroup_selection_id` (`fieldgroup_selection_id`,`fieldgroup_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `form` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort_by` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sort_reverse` tinyint(1) NOT NULL DEFAULT '0',
  `fall_through` enum('none','all','category','box','parent') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  `show_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `fieldgroup_selection_id` int(11) DEFAULT NULL,
  `permission_level` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `show_in_menu` (`show_in_menu`),
  KEY `fieldgroup_selection_id` (`fieldgroup_selection_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `form_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `sup1` int(11) NOT NULL DEFAULT '0',
  `sup2` int(11) NOT NULL DEFAULT '0',
  `sup3` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `sup4` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '50',
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'ef',
  `form_id` int(10) unsigned NOT NULL DEFAULT '0',
  `multi` tinyint(1) NOT NULL DEFAULT '0',
  `language_independent` tinyint(1) NOT NULL DEFAULT '0',
  `add_to_title` tinyint(1) NOT NULL DEFAULT '0',
  `permission_level` int(11) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_name_fid` (`name`,`form_id`),
  KEY `name` (`name`),
  KEY `weight` (`weight`),
  KEY `type` (`type`),
  KEY `form_id` (`form_id`),
  KEY `language_independent` (`language_independent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `journal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_change` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `content_id` (`content_id`),
  KEY `user_id` (`user_id`),
  KEY `last_change` (`last_change`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `languages` (
  `lg` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`lg`),
  KEY `weight` (`weight`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci PACK_KEYS=0;


CREATE TABLE `message` (
  `message_id` int(11) NOT NULL AUTO_INCREMENT,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`message_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `node` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `form_id` int(10) unsigned DEFAULT NULL,
  `childform_id` int(10) unsigned DEFAULT NULL,
  `contentform_id` int(10) unsigned DEFAULT NULL,
  `box_depth` int(10) unsigned NOT NULL DEFAULT '0',
  `weight` int(10) unsigned NOT NULL DEFAULT '10',
  `access_restricted` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_change` int(10) unsigned DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cache_active` tinyint(1) DEFAULT NULL,
  `cache_childform_id` int(11) DEFAULT NULL,
  `cache_contentform_id` int(11) DEFAULT NULL,
  `cache_form_id` int(11) DEFAULT NULL,
  `cache_depth` int(11) DEFAULT NULL,
  `cache_box_depth` int(11) DEFAULT NULL,
  `cache_access_restricted_node_id` int(11) DEFAULT NULL,
  `cache_left_index` int(11) DEFAULT NULL,
  `cache_right_index` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `cache_left_index` (`cache_left_index`,`cache_right_index`),
  KEY `parent_id` (`parent_id`),
  KEY `form_id` (`form_id`),
  KEY `childform_id` (`childform_id`),
  KEY `contentform_id` (`contentform_id`),
  KEY `box_depth` (`box_depth`),
  KEY `weight` (`weight`),
  KEY `access_restricted` (`access_restricted`),
  KEY `active` (`active`),
  KEY `cache_active` (`cache_active`),
  KEY `cache_childform_id` (`cache_childform_id`),
  KEY `cache_contentform_id` (`cache_contentform_id`),
  KEY `cache_form_id` (`cache_form_id`),
  KEY `cache_depth` (`cache_depth`),
  KEY `cache_box_depth` (`cache_box_depth`),
  KEY `cache_access_restricted_node_id` (`cache_access_restricted_node_id`),
  KEY `cache_left_index_2` (`cache_left_index`),
  KEY `cache_right_index` (`cache_right_index`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `update_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` int(10) unsigned NOT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  `module` text COLLATE utf8_unicode_ci NOT NULL,
  `success` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_salt` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL,
  `adminLanguage` enum('de','fr','en') COLLATE utf8_unicode_ci NOT NULL,
  `defaultLanguage` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL,
  `activation_permission` tinyint(1) NOT NULL DEFAULT '1',
  `delete_permission` tinyint(1) NOT NULL DEFAULT '1',
  `copy_permission` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `password` (`password`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `users2languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `lg` char(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_lg_user` (`userId`,`lg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `users2modules` (
  `userId` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  PRIMARY KEY (`userId`,`moduleId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `users2nodes` (
  `userId` int(11) NOT NULL,
  `nodeId` int(11) NOT NULL,
  PRIMARY KEY (`userId`,`nodeId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `wording` (
  `lg` char(2) COLLATE utf8_unicode_ci NOT NULL,
  `keyword` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `translation` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`keyword`,`lg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 2012-07-04 19:04:50
