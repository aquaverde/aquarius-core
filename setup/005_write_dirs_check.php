<?php

$require_writable = array('', 'cache'); // Relative to install dir
foreach($require_writable as $dir) {
    $fulldir = $install_dir.$dir;
    if (!is_dir($fulldir)) {
        $success = mkdir($fulldir);
        if ($success) message('', "Aquarius ".($dir?"'$dir'":"")." directory created. ($fulldir)");
        else {
            message('warn', "Unable to create Aquarius directory ".($dir?"'$dir'":"").", full path $fulldir.");
            $halt = true;
            break;
        }
    }
    if (!is_writable($fulldir)) {
        message('warn', "Unable to write to Aquarius directory ".($dir?"'$dir'":"").", full path $fulldir.");
        $halt = true;
    }
}

if ($halt) "<form action=''><input type='submit' value='Retry'/></form>";