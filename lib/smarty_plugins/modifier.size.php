<?php
/*
 * size, in kB or MB. returns e.g. "309 kB", "1.13 MB"
 */

function smarty_modifier_size($string) {
    $file = FILEBASEDIR.$string; 

    if (file_exists($file)) {
        $numBytes = intval(filesize($file) * 0.001); 
        if ($numBytes >= 1000) {
            return sprintf("%.2f&nbsp;MB", $numBytes / 1000); 
        }
        return $numBytes.'&nbsp;kB';
    }

    return ''; 
}