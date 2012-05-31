-- content_field_value.value is sometimes queried for pointing values.
-- Because it's type text we have to specify a prefix length.
-- 8 bytes should be enough for most lookups
CREATE INDEX value_index ON content_field_value (value(8));

-- more indexing
ALTER TABLE `journal` ADD INDEX ( `content_id` );
ALTER TABLE `journal` ADD INDEX ( `user_id` );
ALTER TABLE `journal` ADD INDEX ( `last_change` ); 

-- remove duplicate index
ALTER TABLE `node` DROP INDEX `depth`;
