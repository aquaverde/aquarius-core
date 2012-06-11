CREATE TABLE `file_legend` (
  `file` varchar(255) collate utf8_unicode_ci NOT NULL,
  `lg`   varchar(2)   collate utf8_unicode_ci,
  `legend` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`file`, `lg`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
