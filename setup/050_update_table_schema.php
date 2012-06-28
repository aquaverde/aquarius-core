<?php
/** Merge database config files into one config file.
 * 
 * The files $m/lib/db/schema.ini (where $m is the core path or a module path)
 * are concatenated and written to core/lib/db/merged_schema.ini.
 * 
 * The PEAR DB_DataObject library requires a configuration file with all tables
 * listed. However, in Aquarius, some tables are not managed by core, but 
 * created by modules. These tables must be configured as well, the modules
 * provide schema definitions, which are merged in this step.
 */

// Hack: make $aquarius look like a module for our purposes
$aquarius->path = $aquarius->core_path;
$modules = array(
    'core' => $aquarius
);

$aquarius_loader->init('modules');
$modules = array_merge($modules, $aquarius->modules);

$current_schema = "";
$schema_path = $aquarius->cache_path()."schema.ini";
if (file_exists($schema_path)) {
    $current_schema = file_get_contents($schema_path);
}

$merged_schema = "";
$merged_count = 0;
foreach($modules as $name => $module) {
    $new_schema_path = $module->path."lib/db/schema.ini";
    if (file_exists($new_schema_path)) {
        $merged_schema .= "; tables for $name\n".file_get_contents($new_schema_path)."\n\n";
        $merged_count += 1;
    }
}

if ($merged_schema !== $current_schema) {
    $success = file_put_contents($schema_path, $merged_schema);
    if (!$success) throw new Exception("Failed writing merged schema to $schema_path");
    Log::info("Merged $merged_count DB schema cponfiguration into $schema_path");
    message('', "Updated DB schema");
}