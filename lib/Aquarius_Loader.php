<?php

/** Load and initialize an Aquarius instance
  *
  * Aquarius is loaded in multiple stages. Each stage has two procedures that
  * are executed in order. Theses procedures are:
  * - init(), where the stage includes libraries and prepares its data
  * - load(), where the stage effects the aquarius environment
  *
  * Of these procedures, only load() will be run on every load of aquarius. The data
  * structures loaded by init() must be serializable, so that the stage can be
  * cached.
  * 
  * Loading Aquarius affects the PHP environment. A lot of classes and functions
  * will be defined, some globals and class variables modified, some PHP
  * behaviour changed.
  */
class Aquarius_Loader {
    var $stage_loader;
    var $stages = array();
    var $stages_initialized = array();
    var $stages_loaded = array();
    
    var $include_paths = array();
    var $included_files = array();

    var $aquarius;
    var $db_legacy;
    var $db_pear;

    
    function use_stage($stage_name) {
        if (isset($this->stages[$stage_name])) return $this->stages[$stage_name];
        $stage = $this->prepare($stage_name);
        $this->stages[$stage_name] = $stage;
        $this->stages_initialized[$stage_name] = false;
        $this->stages_loaded[$stage_name] = false;
        return $stage;
    }
    
    /** Load an aquarius stage by name */
    function load($stage_name) {
        if (isset($this->stages_loaded[$stage_name]) && $this->stages_loaded[$stage_name]) return;
        $stage = $this->use_stage($stage_name);
        
        foreach($stage->depends() as $dep) {
            $this->load($dep);
        }
        
        $have_logging = !empty($this->stages_loaded['basic_logging']) || !empty($this->stages_loaded['logging']);
        
        
        if (!isset($this->stages_initialized[$stage_name]) || !$this->stages_initialized[$stage_name]) {
            if ($have_logging) Log::debug("Aquarius initializing stage $stage_name");
            $stage->init($this);
            $this->stages_initialized[$stage_name] = true;
        }
       
        if ($have_logging) Log::debug("Aquarius loading stage $stage_name");
        
        $stage->load($this);
        $this->stages_loaded[$stage_name] = true;
    }
    
    function include_paths($add_paths) {
        $this->include_paths = array_merge($this->include_paths, $add_paths);

        $result = set_include_path($this->include_paths_str());
        if ($result === false) throw new Exception("Unable to set include path to ".$this->include_paths_str());
    }
    
    function include_paths_str() {
        return join(PATH_SEPARATOR, $this->include_paths);
    }
    
    function include_file($file) {
        $result = include_once $file;
        if ($result === false) throw new Exception("Failure to include file $file");
        
        $this->included_files []= $file;
    }   
    
    function prepare($stage_name) {
        $prefix = "Aquarius_Stage_";
        $class_name = $prefix.$stage_name;

        if (!class_exists($class_name)) {
            throw new Exception("Stage $stage_name could not be found");
        }
        return new $class_name();
    }
}


interface Aquarius_Stage {
    function depends();
    function init($loader);
    function load($loader);
}



class Aquarius_Basic_Stage implements Aquarius_Stage {
    function depends() { return array(); }
    function init($loader) {}
    function load($loader) {}
}

/** Configure important paths and setup PHP include path */
class Aquarius_Stage_Paths extends Aquarius_Basic_Stage {
    var $name = 'paths';
    var $core_path;
    var $aquarius_path;
    var $root_path;
    var $include_paths;
    
    function init($loader) {
        $this->core_path =     dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR;
        $this->aquarius_path = dirname($this->core_path).DIRECTORY_SEPARATOR;
        $this->root_path =     dirname($this->aquarius_path).DIRECTORY_SEPARATOR;
        $this->install_path =  $this->aquarius_path;
        
        $lib_path = $this->core_path.'lib'.DIRECTORY_SEPARATOR;
        $loader->include_paths(array(
            $lib_path,
            $lib_path.'pear'.DIRECTORY_SEPARATOR,
            $this->core_path // legacy
        ));
    }

    function load($loader) {
        $loader->core_path = $this->core_path;
        $loader->aquarius_path = $this->aquarius_path;
        $loader->root_path = $this->root_path;
        $loader->install_path = $this->install_path;
    }
}

/** Setup logging without relying on configuration */
class Aquarius_Stage_Basic_Logging extends Aquarius_Basic_Stage {
    var $name = 'basic_logging';
    var $logger;
    
    function depends() { return array('paths'); }
    
    function init($loader) { 
        $loader->include_file('log.php');
        $this->logger = new Logger(
            false,
            Log::INFO,
            Log::NEVER,
            Log::NEVER
        );  
    }
    
    function load($loader) {
        Log::$usuallogger = $this->logger;
    }
}

/** Load the Aquarius proper */
class Aquarius_Stage_Aquarius extends Aquarius_Basic_Stage {
    var $aquarius;
    
    function depends() { return array('paths', 'php_basic_settings', 'basic_logging'); }
    
    function init($loader) { 
        $loader->include_file('aquarius.php');
        $loader->include_file('DomainConfigs.php');
        $loader->include_file('url.php');
        
        // Libraries installed via composer
        $libloader = $loader->core_path.'vendor/autoload.php';
        if (file_exists($libloader)) {
            $loader->include_file($libloader);
        }

        
        $this->aquarius = new Aquarius($loader->root_path, $loader->core_path);
    }
    
    function load($loader) {
        $loader->aquarius = $this->aquarius;
        $loader->aquarius->load_configs();
        spl_autoload_register(Aquarius_Autloader::using($loader));
    }
}


class Aquarius_Autloader {
    var $loader;
    
    function using($loader) {
        $autoloader = new self();
        $autoloader->loader = $loader;
        return array($autoloader, 'autoload_class');
    }
    
    /** Autoloader callback to load aquarius classes
      *
      * Classes prefixed with 'db_' are loaded from the lib/db/ directory,
      * other classes from lib/.
      */
    function autoload_class($class_name) {
        $classpath = false;
        if(strpos($class_name, 'db_') === 0) {
           DB_DataObject::_autoloadClass($class_name);
        } else {
            $classpath = $class_name.'.php';
        }
        if ($classpath) {
            Log::backtrace("Autoloading $classpath");
            if (function_exists('stream_resolve_include_path')) { 
                // For PHP 5.3.2 and up
                $fullpath = stream_resolve_include_path($classpath);
                if ($fullpath) {
                    $this->loader->include_file($fullpath);
                }
            } else {
                // Legacy shit
                // DUMB: syntax errors in included files not shown because of '@'
                $success = @include_once($classpath);
                if ($success) {
                    $this->loader->included_files []= $file;
                }
            }
        }
    }
}

/** Standardise the PHP environment a bit, so we can rely on some things */
class Aquarius_Stage_PHP_Basic_Settings extends Aquarius_Basic_Stage {
    function init($loader) {
        $loader->include_file('utility.lib.php');
    }
    
    function load($loader) {
        if (!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);
       
        
        // Make sure we see them errors
        // Unfortunately we can't enable depreciation warnings and strict
        // standard warnings because the PEAR PHP4 compatible classes use
        // call-time pass-by-reference.
        error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);


        // Convince PHP to treat unhandled Exceptions, E_ERROR and E_PARSE like any
        // other error
        set_exception_handler('process_exception');
        if (version_compare(PHP_VERSION, '5.2') >= 0) register_shutdown_function('handle_fatal_errors');
        
        
        // This does not belong. You idle? Remove it.
        $aquarius_version = array(3, 6, 5);
        define('AQUARIUS_VERSION', join('.', $aquarius_version));

        // Turn on output buffering, so that later code may change headers
        ob_start();

        // We use UTF8 exclusively
        mb_internal_encoding('UTF-8');

        date_default_timezone_set('UTC');
    }
}    


/** Configure PHP according to Aquarius config */
class Aquarius_Stage_PHP_Settings extends Aquarius_Basic_Stage {
    function load($loader) {
        date_default_timezone_set($loader->aquarius->conf('timezone'));
    }
}


class Aquarius_Stage_db_connection extends Aquarius_Basic_Stage {
    var $db_options;
    
    function depends() { return array('aquarius'); }
    function init($loader) {
        $loader->include_file('DB/DataObject.php');

        // Setup PEAR DB_DataObject
        $dbconf = $loader->aquarius->conf('db');
        $port = get($dbconf, 'port');
        $ports = $port ? ":$port" : '';
        $this->db_options = array(
            'database'         => $dbconf['driver'].'://'.$dbconf['user'].':'.DB_PASSWORD.'@'.$dbconf['host'].$ports.'/'.$dbconf['name'],
        //  'database_global'  => 'mysql://'.GLOBALDB_USERNAME.':'.GLOBALDB_PASSWORD.'@'.DB_HOST.'/'.GLOBALDB_DBNAME,
        //  'schema_location'  => PROJECT_INCLUDE_PATH.'lib/db/',
            'ini_'.$dbconf['name'] => $loader->aquarius->cache_path().'schema.ini', // Explicit schema location
            'class_location'       => 'db/',
            'class_prefix'         => 'db_',
            'debug'                => PEARLOGLEVEL
        );
    }
    
    function load($loader) {
        // Tell PEAR to throw errors instead of returning obscure error objects.
        // At least we will notice something's awry that way.
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'pear_error_to_exception');
        
        $pear_options = &PEAR::getStaticProperty('DB_DataObject','options');
        $pear_options = $this->db_options;

        // Force PEAR to initialize the DB connection, we want to use it seperately as well
        $node = DB_DataObject::factory('node');
        $loader->aquarius->db = new DBwrap($node->getDatabaseConnection());
        $loader->aquarius->db->reset_charset(); // Told you so
    }
}

class Aquarius_Stage_legacy_db_connection extends Aquarius_Basic_Stage {
    var $db_options;
    
    function depends() { return array('aquarius'); }
    function init($loader) {
        $loader->include_file('sql.lib.php');
    }
    
    function load($loader) {
        $dbconf = $loader->aquarius->conf('db');
        $loader->db_legacy = new SQLwrap($dbconf['host'], $dbconf['user'], DB_PASSWORD, $dbconf['name']);
        $GLOBALS['DB'] = $loader->db_legacy;
    }
}


class Aquarius_Stage_modules extends Aquarius_Basic_Stage {
  
    function init($loader) {
        $loader->include_file('Module.php');
        $loader->include_file('Module_Manager.php');
        
        /* The modules directory of the installation is set before the one in 
         * core so that installation-specific modules can override core
         * modules. */
        $modules_paths = array(
            $loader->aquarius->install_path.'modules'.DIRECTORY_SEPARATOR,
            $loader->aquarius->core_path.'modules'.DIRECTORY_SEPARATOR,
        );

        $loader->aquarius->module_manager = new Module_Manager($modules_paths);

        try {
            $loader->aquarius->modules = $loader->aquarius->module_manager->load_active_modules($loader);
        } catch(No_Such_Module_Exception $e) {
            Log::fail($e);
            Log::warn("Module missing, trying to rescue by updating module list");
            $loader->aquarius->module_manager->update_list($remove_only = true);
            
            // If it fails again we let it fail
            $loader->aquarius->modules = $loader->aquarius->module_manager->load_active_modules($loader);
        }
    }
}


class Aquarius_Stage_Logging extends Aquarius_Basic_Stage {
    var $logging_manager;
    
    function depends() { return array('aquarius'); }
    
    function init($loader) {
        $loader->include_file('Logging_Manager.php');
        $this->logging_manager = new Logging_Manager(ECHOKEY, $loader->aquarius->conf('log'), $loader->install_path);   
    }

    function load($loader) {
        $logger = $this->logging_manager->load_for(clean_magic($_COOKIE));
        $loader->aquarius->logging_manager = $this->logging_manager;
        $loader->aquarius->logger = $logger;
        Log::$usuallogger = $logger;
        
        // Display PHP errors and warnings when echolevel is on debug
        if ($loader->aquarius->logger->echolevel < Log::INFO || $loader->aquarius->logger->firelevel < Log::INFO) {
            ini_set('display_errors','1');
        } else {
            // Don't change preset
        }
    }
}


class Aquarius_Stage_globals extends Aquarius_Basic_Stage {
    function load($loader) {
        $GLOBALS['aquarius'] = $loader->aquarius;
    }
}


class Aquarius_Stage_full extends Aquarius_Basic_Stage {
    function depends() {
        return array(
            'logging',
            'db_connection',
            'legacy_db_connection',
            'php_settings',
            'modules',
            'globals'
        );
    }
}

    