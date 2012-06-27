# INDEXES !!!!11
ALTER TABLE `dynform` ADD INDEX ( `node_id` );

ALTER TABLE `dynform_block` ADD INDEX ( `dynform_id` );

ALTER TABLE `dynform_entry_data` ADD INDEX ( `entry_id` );
ALTER TABLE `dynform_entry_data` ADD INDEX ( `field_id` );

ALTER TABLE `dynform_entry` ADD INDEX ( `dynform_id` );
ALTER TABLE `dynform_entry` ADD INDEX ( `lg` );

ALTER TABLE `dynform_field_data` ADD INDEX ( `field_id` );
ALTER TABLE `dynform_field_data` ADD INDEX ( `lg` );