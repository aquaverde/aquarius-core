<?php

class Aquarius_Frontloader {
    function __construct() {
        $this->cache_file = dirname(__FILE__).'/../../cache/frontloader.php';
    }

    /** Create an aquarius loader and load aquarius to the given stage
      * The result is cached and reused on later calls to this method
      * 
      * @return aquarius loader
      * 
      */
    function load($stage_name) {
        require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Aquarius_Loader.php';
        
        $loader = false;
        if (file_exists($this->cache_file)) {
            try {
                $loader = include($this->cache_file);
            } catch (Exception $e) {
                unlink($this->cache_file);
            }
        }

        $rebuild = !$loader;
        $except_includes = array();
        if ($rebuild) {
            $loader = new Aquarius_Loader();
        }

        $loader->load($stage_name);

        if ($rebuild && $loader->aquarius->conf('initcache')) {
            $this->cache($loader);
        }

        return $loader;
    }
    
    function cache($loader) {
        $frontloader = '<?php
function frontinclude($file) {
    $success = include $file;
    if (!$success) throw new Exception("Frontload failure to load $file");
}

$include_paths = '.var_export($loader->include_paths_str(), true).';
$success = set_include_path($include_paths);
if (!$success) throw new Exception("Unable to set include path $include_paths");';
        foreach($loader->included_files as $included) {
            $frontloader .= '
frontinclude('.var_export($included, true).');';
        }
        
        // Prepare a loader that is serializable
        $ser_loader = clone $loader;
        $ser_loader->stages_loaded = array();
        
        $frontloader .= '
return unserialize('.var_export(serialize($ser_loader), true).');
';
        $frontload_file_new = $this->cache_file.'.'.uniqid();
        $success = file_put_contents($frontload_file_new, $frontloader);
        if ($success === false) throw new Exception("Unable to write to $frontload_file_new");
        rename($frontload_file_new, $this->cache_file); // Atomic I presume
        Log::debug("Wrote frontloader to $this->cache_file");
    }
    
    function delete_cache() {
        if (file_exists($this->cache_file)) unlink($this->cache_file);
    }
}