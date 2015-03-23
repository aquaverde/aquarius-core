<?php

/* If dynform template dir does not exist, create it */
$template_dir = $aquarius->install_path.'templates/';

$dynform_template_dir = $template_dir.'dynform/';
if (!is_dir($dynform_template_dir)) {
    $success = mkdir($dynform_template_dir, 0777 & fileperms($template_dir), true);
    if (!$success) {
        throw new Exception("Unable to create $dynform_template_dir");
    }
}

/* Legacy dynform files placed directly in templates dir are moved to the new subdir. */
foreach(glob($template_dir.'dynform.*.tpl') as $old_location) {
    $old_path = dirname($old_location);
    $old_name = basename($old_location);
    $new_name = $old_name;
    $new_name[7] = '/'; // Put a slash where the dot is, haha so cheap
    $new_location = $old_path.'/'.$new_name;
    $success = rename($old_location, $new_location);
    if (!$success) {
        throw new Exception("Unable to move $old_name to $new_name");
    }
    
    if ($old_name == 'dynform.form.tpl') {
        // Ugh, patch the file
        $formcode = file_get_contents($new_location);
        $new_formcode = preg_replace('%{include file="dynform.%', '{include file="dynform/', $formcode);
        $success = file_put_contents($new_location, $new_formcode);
        if (!$success) throw new Exception("Unable to patch $new_name");
    }
}
