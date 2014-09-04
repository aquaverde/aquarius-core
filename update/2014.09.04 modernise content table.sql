ALTER TABLE `content_field` DROP `value`;
ALTER TABLE `content` CHANGE `cache_fields` `cache_fields` mediumblob NULL AFTER `cache_title`;