CREATE TABLE `form_child` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `parent_id` int NOT NULL,
  `child_id` int NOT NULL,
  `preset` tinyint unsigned NOT NULL
) COMMENT='' ENGINE='InnoDB';

UPDATE node SET form_id = cache_form_id WHERE form_id = 0 OR ISNULL(form_id);

ALTER TABLE `node` 
    DROP `cache_form_id`,
    DROP `cache_childform_id`,
    DROP `cache_contentform_id`;