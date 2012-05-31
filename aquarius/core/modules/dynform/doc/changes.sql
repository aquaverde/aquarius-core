DROP TABLE `dynform_fields`


CREATE TABLE `dynform_field` (
  `dynform_field_id` int(10) unsigned NOT NULL auto_increment,
  `dynform_block_id` int(10) unsigned NOT NULL default '0',
  `field_type` enum('SINGLELINE','MULTILINE','CHECKBOX','RADIO','DROPDOWN','COMMENT') collate utf8_unicode_ci NOT NULL default 'SINGLELINE',
  `field_name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `field_size` tinyint(4) NOT NULL default '0',
  `field_extratext` mediumtext collate utf8_unicode_ci NOT NULL,
  `field_imagelink` varchar(255) collate utf8_unicode_ci default NULL,
  `field_downloadlink` varchar(255) collate utf8_unicode_ci default NULL,
  `field_weight` int(11) NOT NULL default '0',
  `field_required` enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
  `field_options` mediumtext collate utf8_unicode_ci,
  PRIMARY KEY  (`dynform_field_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=40 ;