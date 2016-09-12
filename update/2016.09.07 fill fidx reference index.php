<?php

// We need all modules initialized because some add their own formtypes
$GLOBALS['aquarius_loader']->load('full');
$GLOBALS['aquarius']->load();

// Because this is very slow and might get interrupted on large codebases
// we record successful updates
$db = $aquarius->db;
$db->query("
    CREATE TABLE IF NOT EXISTS fill_idx_ref_temp (
        content_id INT
    );
");


// This will be zero on the first run
$last_updated_content = intval($db->singlequery("SELECT MAX(content_id) FROM fill_idx_ref_temp"));


// Re-save all content fields so the new 'fidx' fields are written
$content = new db_Content();
$content->orderBy('id');
$content->whereAdd('id > '.$last_updated_content);
$content->find();
while($content->fetch()) {
    echo "Updating $content->cache_title ($content->id)<br>";
    flush();
    $content->load_fields();
    $content->save_content();
     $db->query("INSERT INTO fill_idx_ref_temp SET content_id = ?", array($content->id));
}

// Succeeded! Remove table
$db->query("
    DROP TABLE fill_idx_ref_temp;
");