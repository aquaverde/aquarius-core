<?php

// We need all modules initialized because some add their own formtypes
$GLOBALS['aquarius_loader']->load('full');
$GLOBALS['aquarius']->load();

// Re-save all content fields so the new 'fidx' fields are written
$content = new db_Content();
$content->find();
while($content->fetch()) {
    $content->load_fields();
    $content->save_content();
}
