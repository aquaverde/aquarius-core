CREATE TABLE update_log (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`date` INT UNSIGNED NOT NULL ,
`name` TEXT NOT NULL ,
`module` TEXT NOT NULL
) ENGINE = MYISAM ;

ALTER TABLE `update_log` ADD `success` TINYINT;

-- For upgrades some updates should not be reapplied
INSERT INTO `update_log` (`id`, `date`, `name`, `module`) VALUES
(4, 1316624345, '2011 Create dynform tables.sql', 'dynform')