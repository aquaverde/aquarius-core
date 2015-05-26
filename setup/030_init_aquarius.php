<?php

require_once '../lib/Aquarius_Loader.php';
$aquarius_loader = new Aquarius_loader();
$aquarius_loader->load('aquarius');
$aquarius_loader->load('globals');

$cached_db_schema = $aquarius->cache_path().'schema.ini';
if (!file_exists($cached_db_schema)) {
    message('', "Copying initial DB schema");
    copy($aquarius->core_path.'lib/db/schema.ini', $cached_db_schema);
}

$aquarius_loader->load('db_connection');


$confd_path = $aquarius->install_path."conf.d/";
if(file_exists($confd_path)) {
    message('warn', "The config files in conf.d are not read anymore. Copy all settings into config.php. Delete the conf.d directory to get rid of this message.");
}

