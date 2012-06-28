<?php

require_once '../lib/Aquarius_Loader.php';
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

$cached_db_schema = $aquarius->cache_path().'schema.ini';
if (!file_exists($cached_db_schema)) {
    message('', "Copying initial DB schema");
    copy($aquarius->core_path.'lib/db/schema.ini', $cached_db_schema);
}

$aquarius_loader->init(
    'establish_db_connection',
    'configure_logging',
    'load_libs',
    'GLOBALS'
);
