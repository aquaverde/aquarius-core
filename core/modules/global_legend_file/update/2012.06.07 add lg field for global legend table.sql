ALTER TABLE `file_legend` ADD `lg` varchar(2) collate utf8_unicode_ci;

ALTER TABLE `file_legend`
ADD PRIMARY KEY `file_lg` (`file`, `lg`),
DROP INDEX `PRIMARY`;
