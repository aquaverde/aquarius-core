
-- --------------------------------------------------------

-- 
-- Structure de la table `dynform`
-- 

CREATE TABLE `dynform` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Contenu de la table `dynform`
-- 

INSERT INTO `dynform` (`id`, `node_id`) VALUES 
(1, 6);

-- --------------------------------------------------------

-- 
-- Structure de la table `dynform_block`
-- 

CREATE TABLE `dynform_block` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dynform_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Contenu de la table `dynform_block`
-- 

INSERT INTO `dynform_block` (`id`, `dynform_id`, `name`, `weight`) VALUES 
(1, 1, 'Adresse', 10);

-- --------------------------------------------------------

-- 
-- Structure de la table `dynform_block_data`
-- 

CREATE TABLE `dynform_block_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `block_id` int(10) unsigned NOT NULL,
  `lg` char(2) collate utf8_unicode_ci NOT NULL,
  `name` mediumtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- 
-- Contenu de la table `dynform_block_data`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `dynform_entry`
-- 

CREATE TABLE `dynform_entry` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `dynform_id` int(11) NOT NULL,
  `lg` char(2) collate utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  `submitnodetitle` mediumtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=46 ;

-- 
-- Contenu de la table `dynform_entry`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `dynform_entry_data`
-- 

CREATE TABLE `dynform_entry_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `entry_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `name` mediumtext collate utf8_unicode_ci NOT NULL,
  `value` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=109 ;

-- 
-- Contenu de la table `dynform_entry_data`
-- 


-- --------------------------------------------------------

-- 
-- Structure de la table `dynform_field`
-- 

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- 
-- Contenu de la table `dynform_field`
-- 

INSERT INTO `dynform_field` (`id`, `block_id`, `type`, `name`, `weight`, `required`, `num_lines`, `width`) VALUES 
(1, 1, 1, 'Name', 10, 1, 0, 0),
(2, 1, 1, 'Vorname', 20, 1, 0, 0),
(3, 1, 1, 'Adresse', 30, 0, 0, 0),
(4, 1, 1, 'PLZ', 40, 0, 0, 0),
(5, 1, 1, 'Ort', 50, 0, 0, 0),
(6, 1, 1, 'tel', 60, 0, 0, 0),
(7, 1, 7, 'E-Mail', 70, 1, 0, 0),
(8, 1, 2, 'Mitteilungen', 80, 0, 0, 0);

-- --------------------------------------------------------

-- 
-- Structure de la table `dynform_field_data`
-- 

CREATE TABLE `dynform_field_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `field_id` int(10) unsigned NOT NULL default '0',
  `lg` char(2) collate utf8_unicode_ci NOT NULL default '0',
  `name` mediumtext collate utf8_unicode_ci NOT NULL,
  `options` longtext collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

-- 
-- Contenu de la table `dynform_field_data`
-- 

INSERT INTO `dynform_field_data` (`id`, `field_id`, `lg`, `name`, `options`) VALUES 
(1, 1, 'de', 'Name', ''),
(2, 1, 'fr', 'Name', ''),
(3, 2, 'de', 'Vorname', ''),
(4, 2, 'fr', 'Vorname', ''),
(5, 3, 'de', 'Adresse', ''),
(6, 3, 'fr', 'Adresse', ''),
(7, 4, 'de', 'PLZ', ''),
(8, 4, 'fr', 'PLZ', ''),
(9, 5, 'de', 'Ort', ''),
(10, 5, 'fr', 'Ort', ''),
(11, 6, 'de', 'tel', ''),
(12, 6, 'fr', 'tel', ''),
(13, 7, 'de', 'E-Mail', ''),
(14, 7, 'fr', 'E-Mail', ''),
(15, 8, 'de', 'Mitteilungen', ''),
(16, 8, 'fr', 'Mitteilungen', '');

-- --------------------------------------------------------

-- 
-- Structure de la table `dynform_field_type`
-- 

CREATE TABLE `dynform_field_type` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `default_template` varchar(255) collate utf8_unicode_ci NOT NULL,
  `template` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- 
-- Contenu de la table `dynform_field_type`
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

-- --------------------------------------------------------

-- 
-- Structure de la table `dynform_settings`
-- 

CREATE TABLE `dynform_settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `keyword` varchar(256) collate utf8_unicode_ci NOT NULL,
  `value` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Contenu de la table `dynform_settings`
-- 

