<?php
require_once 'Aquarius_Loader.php';

class Aquarius_Frontloader {
    function __construct() {
        $this->cache_file = dirname(__FILE__).'/../../cache/frontloader.php';
    }

    
    function load($stage_name) {
        $loader = false;
        if (file_exists($this->cache_file)) {
            try {
                $loader = include($this->cache_file);
            } catch (Exception $e) {
                delete($this->cache_file);
            }
        }

        $rebuild = !$loader;
        $except_includes = array();
        if ($rebuild) {
            $loader = new Aquarius_Loader();
        }

        $loader->load($stage_name);

        if ($rebuild) {
            $this->cache($loader);
        }
    }
    
    function cache($loader) {
        $frontloader = '<?php
function frontinclude($file) {
    $success = @include $file;
    if (!$success) throw new Exception("Frontload failure to load $file");
}

$loader = false;
$frontloader_failure = false;
try {';
        
        foreach(get_included_files() as $included_file) {
            $frontloader .= '
    frontinclude('.var_export($included_file, true).');';
        }
        
        // Prepare a loader that is serializable
        $ser_loader = clone $loader;
        $ser_loader->db_pear = false;
        $ser_loader->db_legacy = false;
        $ser_loader->stages_loaded = array();
        
        $frontloader .= '
    $loader = unserialize('.var_export(serialize($ser_loader), true).');
} catch (Exception $e) {
    $loader = false;
    $frontloader_failure = $e;
}
';
        $frontload_file_new = $frontload_file.'.'.uniqid();
        file_put_contents($frontload_file_new, $frontloader);
        rename($frontload_file_new, $frontload_file); // Atomic I presume
        Log::debug("Wrote frontloader to $frontload_file");
    }
}