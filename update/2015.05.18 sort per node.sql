ALTER TABLE `node` ADD `sort_by` varchar(255) COLLATE 'utf8_unicode_ci' NULL;
ALTER TABLE `node` ADD `sort_reverse` tinyint(1) unsigned NOT NULL DEFAULT '0';

INSERT INTO message (text)
SELECT DISTINCT CONCAT("Nodes with form '", cf.title, "' are sorted by '", cf.sort_by, "'. This must now be configured in the parent form '", pf.title, "' (", pf.id, ") or in node '", pn.title, "' (", pn.id, "). You can also set $config['admin']['classic_sort'] = true; to keep the old behaviour.")
FROM form pf
JOIN node pn ON pf.id = pn.form_id
JOIN node cn ON pn.id = cn.parent_id
JOIN form cf ON cf.id = cn.form_id
WHERE LENGTH(cf.sort_by) > 0
ORDER BY pn.title, cf.title;