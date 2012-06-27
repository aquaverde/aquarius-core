<?php

require_once '../core/Aquarius_Loader.php';
$aquarius_loader = new Aquarius_loader();
$aquarius = $aquarius_loader->init(
    'basic_logging',
    'error_reporting',
    'create_aquarius',
    'establish_db_connection',
    'configure_logging',
    'load_libs',
    'GLOBALS'
);
