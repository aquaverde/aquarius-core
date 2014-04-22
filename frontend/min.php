<?php
$coredir = dirname(__DIR__);
require_once $coredir.'/vendor/autoload.php';
define('MINIFY_MIN_DIR', $coredir.'/vendor/mrclay/minify/min/lib');
define('CACHE_DIR', dirname($coredir).'/cache/min');

// setup include path
set_include_path(MINIFY_MIN_DIR . PATH_SEPARATOR . get_include_path());

Minify::setCache(CACHE_DIR, true);

require_once 'Minify/DebugDetector.php';
$debug =  Minify_DebugDetector::shouldDebugRequest($_COOKIE, $_GET, $_SERVER['REQUEST_URI']);

if (!$debug) {
    ini_set('display_errors', 0);
}

$min_serveOptions = array(
    'bubbleCssImports' => true,
    'debug' => $debug,
    'fileLocking' => false
);

if ($debug) {
    require_once 'Minify/Logger.php';
    require_once 'FirePHP.php';

    Minify_Logger::setLogger(FirePHP::getInstance(true));
}

// check for URI versioning
if (preg_match('/&\\d/', $_SERVER['QUERY_STRING'])) {
    $min_serveOptions['maxAge'] = 31536000;
}

if (isset($_GET['f'])) {
    // serve!   
    require 'Minify/Controller/MinApp.php';
    $min_serveController = new Minify_Controller_MinApp();

    Minify::serve($min_serveController, $min_serveOptions);

} else {
    header("Location: /");
    exit();
}