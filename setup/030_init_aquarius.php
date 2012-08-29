<?php

require_once '../lib/Aquarius_Loader.php';
$aquarius_loader = new Aquarius_loader();
$aquarius_loader->load('aquarius');
$aquarius_loader->load('db_connection');
$aquarius_loader->load('globals');

$cached_db_schema = $aquarius->cache_path().'schema.ini';
if (!file_exists($cached_db_schema)) {
    message('', "Copying initial DB schema");
    copy($aquarius->core_path.'lib/db/schema.ini', $cached_db_schema);
}

$aquarius_loader->load('full');

