CREATE TABLE `dynform` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

CREATE TABLE `dynform_block` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dynform_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

CREATE TABLE `dynform_block_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `block_id` int(10) unsigned NOT NULL,
  `lg` char(2) collate utf8_unicode_ci NOT NULL,
  `name` mediumtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

CREATE TABLE `dynform_entry` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dynform_id` int(11) NOT NULL,
  `lg` char(2) collate utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  `submitnodetitle` mediumtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=21 ;

CREATE TABLE `dynform_entry_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entry_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `name` mediumtext collate utf8_unicode_ci NOT NULL,
  `value` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=144 ;

CREATE TABLE `dynform_field` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `block_id` int(10) unsigned NOT NULL default '0',
  `type` int(11) NOT NULL,
  `name` mediumtext collate utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL default '0',
  `num_lines` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=40 ;

CREATE TABLE `dynform_field_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `lg` char(2) collate utf8_unicode_ci NOT NULL default '0',
  `name` mediumtext collate utf8_unicode_ci NOT NULL,
  `options` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=53 ;


-- 
-- Table structure for table `dynform_field_type`
-- 

CREATE TABLE `dynform_field_type` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `default_template` varchar(255) collate utf8_unicode_ci NOT NULL,
  `template` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

-- 
-- Dumping data for table `dynform_field_type`
-- 

INSERT INTO `dynform_field_type` (`id`, `name`, `default_template`, `template`) VALUES 
(1, 'Singleline', 'df_singleline', ''),
(2, 'Multiline', 'df_multiline', ''),
(3, 'Checkbox', 'df_checkbox', ''),
(4, 'Pulldown', 'df_pulldown', ''),
(5, 'Radiobutton', 'df_radiobutton', ''),
(6, 'Text', 'df_text', ''),
(7, 'Email', 'df_email', ''),
(8, 'Number', 'df_number', '');

