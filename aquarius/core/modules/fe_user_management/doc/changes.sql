

ALTER TABLE `fe_address` ADD `zip` VARCHAR( 10 ) NOT NULL AFTER `address` ;
ALTER TABLE `fe_address` CHANGE `zipcity` `city` VARCHAR( 250 ) NOT NULL;

ALTER TABLE `fe_address` CHANGE `id` `id` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE `firma` `firma` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `country` `country` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `phone` `phone` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `mobile` `mobile` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ;