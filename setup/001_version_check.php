<?php 

// left-overs in PHP 5.3
$rg = strtolower(ini_get('register_globals'));
if ((bool) $rg && $rg !== "off") {
    // What is this I don't even
    message('warn', "Aquarius requires that the obsolete PHP-functionality 'register_globals' be disabled.");
    $halt = true;
}


$required_php_version = '5.3.0';
$actual_php_version = PHP_VERSION;
if (version_compare($actual_php_version, $required_php_version) < 0) {
    message('warn', "PHP version ".$actual_php_version." is not supported anymore, please upgrade. Aquarius requires PHP version $required_php_version or newer.");
    $halt = true;
}


$required_extensions = array('mysqli', 'mbstring', 'gd');
$missing_extensions = array_diff($required_extensions, get_loaded_extensions());
if (!empty($missing_extensions)) {
    message('warn', "The following PHP extensions are required by Aquarius but not installed: ".join(',', $missing_extensions).'.');
    $halt = true;
}