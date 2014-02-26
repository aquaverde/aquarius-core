CREATE TABLE `form_child` (
  `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `parent_id` int NOT NULL,
  `child_id` int NOT NULL,
  `preset` tinyint unsigned NOT NULL
) COMMENT='' ENGINE='InnoDB';
