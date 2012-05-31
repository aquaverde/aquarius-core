-- 2009.11.03/sb Add fields to save product node, attribute node and property node
ALTER TABLE `shop_order_item_attribute`
ADD `attribute_id` INT UNSIGNED NULL ,
ADD `property_id` INT UNSIGNED NULL

ALTER TABLE `shop_order_item`
ADD `product_id` INT UNSIGNED NULL


-- Older changes

ALTER TABLE `shop_order` ADD `sequence_nr` INT NOT NULL AFTER `id` ;

RENAME TABLE `shop_address`  TO `fe_address` ;

CREATE TABLE `fe_user_address` (
`fe_user_id` INT NOT NULL ,
`fe_address_id` INT NOT NULL ,
PRIMARY KEY ( `fe_user_id` , `fe_address_id` )
) ENGINE = MYISAM ;

INSERT INTO fe_user_address (fe_user_id, fe_address_id) SELECT fe_user_id, id FROM fe_address;

DROP TABLE `shop_fe_users`;
DROP TABLE `shop_fe_users_address`;

ALTER TABLE `shop_order` ADD `status` ENUM( 'temporary', 'pending', 'done' ) NOT NULL AFTER `sequence_nr` ;