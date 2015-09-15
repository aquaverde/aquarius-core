ALTER TABLE `node` ADD `sort_by` varchar(255) COLLATE 'utf8_unicode_ci' NULL;
ALTER TABLE `node` ADD `sort_reverse` tinyint(1) unsigned NOT NULL DEFAULT '0';

UPDATE node parent
JOIN node child ON parent.id = child.parent_id
JOIN form child_form ON child.form_id = child_form.id
SET
    parent.sort_by = child_form.sort_by,
    parent.sort_reverse = child_form.sort_reverse
wHERE LENGTH(child_form.sort_by) > 0;

UPDATE form SET sort_by = '', sort_reverse = 0;
