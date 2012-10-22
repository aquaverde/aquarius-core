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
 * 
 * The supplemental schema.links.ini files are also merged.
 */

// Hack: make $aquarius look like a module for our purposes
$aquarius->path = $aquarius->core_path;
$modules = array(
    'core' => $aquarius
);

$aquarius_loader->load('modules');
$modules = array_merge($modules, $aquarius->modules);

$current_schema = "";
$schema_path = $aquarius->cache_path()."schema.ini";
if (file_exists($schema_path)) {
    $current_schema = file_get_contents($schema_path);
}

$current_links_schema = "";
$links_schema_path = $aquarius->cache_path()."schema.links.ini";
if (file_exists($links_schema_path)) {
    $current_links_schema = file_get_contents($links_schema_path);
}

$merged_schema = "";
$merged_links_schema = "";
$merged_count = 0;
foreach($modules as $name => $module) {
    $new_schema_path = $module->path."lib/db/schema.ini";
    if (file_exists($new_schema_path)) {
        $merged_schema .= "; tables for $name\n".file_get_contents($new_schema_path)."\n\n";
        $merged_count += 1;
    }
    $new_links_schema_path = $module->path."lib/db/schema.links.ini";
    if (file_exists($new_links_schema_path)) {
        $merged_links_schema .= "; links for $name\n".file_get_contents($new_links_schema_path)."\n\n";
    }
}

if ($merged_schema !== $current_schema) {
    $success = file_put_contents($schema_path, $merged_schema);
    if (!$success) throw new Exception("Failed writing merged schema to $schema_path");
    Log::info("Merged $merged_count DB schema configurations into $schema_path");
    message('', "Updated DB schema");
}

if ($merged_links_schema !== $current_links_schema) {
    $success = file_put_contents($links_schema_path, $merged_links_schema);
    if (!$success) throw new Exception("Failed writing merged schema to $links_schema_path");
}