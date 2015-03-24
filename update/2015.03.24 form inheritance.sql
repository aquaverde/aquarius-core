ALTER TABLE `form_field`
ADD `inherited` tinyint unsigned NOT NULL DEFAULT '0';

CREATE TABLE `form_inherit` (
  `child_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`child_id`,`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
