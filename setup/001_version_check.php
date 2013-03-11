<?php 

$rg = strtolower(ini_get('register_globals'));
if ((bool) $rg && $rg !== "off") {
    // What is this I don't even
    message('warn', "Aquarius requires that the obsolete PHP-functionality 'register_globals' be disabled.");
    $halt = true;
}

// Just a month ago, we had a request to install Aquarius for PHP4. In 2011.
$required_php_version = '5.2.0';
if (version_compare(PHP_VERSION, $required_php_version) < 0) {
    message('warn', "PHP version ".PHP_VERSION." is too old, please upgrade. Aquarius requires PHP version $required_php_version or higher.  As a gentle reminder: PHP 5.2 was released in 2006, or just about ".floor((time() - strtotime('2006-11-02'))/(60*60*24)).' days ago.');
    $halt = true;
}


$required_extensions = array('mysql', 'mbstring', 'gd');
$missing_extensions = array_diff($required_extensions, get_loaded_extensions());
if (!empty($missing_extensions)) {
    message('warn', "The following PHP extensions are required by Aquarius but not installed: ".join(',', $missing_extensions).'.');
    $halt = true;
}