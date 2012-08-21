<?php

/** Load and initialize an Aquarius instance
  *
  * Loading the different parts of aquarius happens on request, in stages. Every
  * stage is a method of this class. Stages may load other stages they depend
  * on, these will be loaded before the requested stage.
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
    
    var $include_dirs = array();
    var $included_files = array();

    var $aquarius;
    var $db_legacy;
    var $db_pear;
    
    function __construct($stage_loader) {
        $this->stage_loader = $stage_loader;
    }
    
    function use_stage($stage_name) {
        if (isset($this->stages[$stage_name]) return $this->stages[$stage_name];
        $stage = $this->stage_loader->prepare($stage_name);
        $this->stages[$stage_name] = $stage;
        $this->stages_initialized = false;
        $this->stages_loaded = false;
        return $stage;
    }
    
    /** Load an aquarius stage by name */
    function load($stage_name) {
        if (isset($this->stages_loaded[$stage_name]) && $this->stages_loaded[$stage_name]) return;
        $stage = $this->use_stage($stage_name);
        
        foreach($stage->depends() as $dep) {
            $this->load($dep);
        }
        
        $have_logging = isset($this->stages_loaded['basic_logging']) || isset($this->stages_loaded['logging']);
        
        
        if (!$this->stages_initialized[$stage_name]) {
            if ($have_logging) Log::debug("Aquarius initializing stage $stage");
            $stage->init($this);
            $this->stages_initialized[$stage_name] = true;
        }
       
        if ($have_logging) Log::debug("Aquarius loading stage $stage");
        
        $stage->load($this);
        $this->stages_loaded[$stage_name] = true;
    }

    /** Load full aquarius */
    function full() {
        $this->init(
            'basic_logging',
            'error_reporting',
            'basic_settings',
            'create_aquarius',
            'configure_settings',
            'class_autoloader',
            'establish_db_connection',
            'configure_logging',
            'modules',
            'load_libs',
            'GLOBALS',
            'frontloader'
        );
    }
}


















interface Aquarius_Stage {
    function depends();
    function init(Aquarius_Loader $loader);
    function load(Aquarius_Loader $loader);
}

class Aquarius_Stage_Loader() {
    $prefix = "Aquarius_Stage_";
    function prepare($stage_name) {
        $class_name = $prefix.$stage_name;
        if (!class_exists($class_name)) {
            throw new Exception("Stage $stage_name could not be found");
        }
        return new $class_name();
    }
}

class Aquarius_Basic_Stage implements Aquarius_Stage {
    function depends() { return array(); }
    function init(Aquarius_Loader $loader) {}
    function load(Aquarius_Loader $loader) {}
}

class Aquarius_Stage_Paths extends Aquarius_Basic_Stage {
    var $name = 'paths';
    var $core_path;
    var $aquarius_path;
    var $root_path;
    var $include_paths;
    
    function init(Aquarius_Loader $loader) {
        $this->core_path = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR;
        $this->aquarius_path = realpath($loader->core_path.'..').DIRECTORY_SEPARATOR;
        $this->root_path = realpath($loader->core_path.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR;

        $loader->include_path($loader->core_path.'lib'.DIRECTORY_SEPARATOR);
        $loader->include_path($loader->core_path.'lib/');
        $loader->include_path($loader->core_path.'lib/');
        
        $lib_path = $loader->core_path.'lib'.DIRECTORY_SEPARATOR;
        $loader->include_path($lib_path);
        $loader->include_path($lib_path.'pear'.DIRECTORY_SEPARATOR);
        $loader->include_path($this->core_path); // legacy
    }

    function load(Aquarius_Loader $loader) {
        $loader->core_path = $this->core_path;
        $loader->aquarius_path = $this->aquarius_path;
        $loader->root_path = $this->root_path;
        
        $result = set_include_path($this->include_paths);
        if ($result === false) throw new Exception("Unable to set include path.");
    }
}

class Aquarius_Stage_Basic_Logging extends Aquarius_Basic_Stage {
    var $name = 'basic_logging';
    var $logger;
    
    function depends() { return array('paths'); }
    
    function init(Aquarius_Loader $loader) { 
        $loader->include('log.php');
        $this->logger = new Logger(
            false,
            Log::INFO,
            Log::NEVER,
            Log::NEVER
        );  
    }
    
    function load(Aquarius_Loader $loader) {
        Log::$usuallogger = $this->logger;
    }
}

class Aquarius_Stage_Aquarius extends Aquarius_Basic_Stage {
    var $aquarius;
    
    function depends() { return array('paths', 'basic_settings'); }
    
    function init(Aquarius_Loader $loader) { 
        $loader->include('aquarius.php');
        $this->aquarius = new Aquarius($loader->root_path, $loader->core_path);
        $this->aquarius->load_configs();
    }
    
    function load(Aquarius_Loader $loader) {
        $loader->aquarius = $this->aquarius;
        spl_autoload_register(array($this->aquarius, 'autoload_class'));
    }
}

/** Standardise the PHP environment a bit, so we can rely on some things */
class Aquarius_Stage_PHP_Basic_Settings extends Aquarius_Basic_Stage {
    function load() {
        if (!defined(E_DEPRECATED)) define('E_DEPRECATED', 8192);
        
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
        $loader->include('sql.lib.php');
        $loader->include('DB/DataObject.php');

        // Setup PEAR DB_DataObject
        $dbconf = $this->aquarius->conf('db');
        $this->db_options = array(
            'database'         => 'mysqli://'.$dbconf['user'].':'.DB_PASSWORD.'@'.$dbconf['host'].'/'.$dbconf['name'],
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
        
        // Legacy DB connection
        $loader->db_legacy = new SQLwrap($dbconf['host'], $dbconf['user'], DB_PASSWORD, $dbconf['name']);
    }
}


class Aquarius_Stage_modules extends Aquarius_Basic_Stage {
    
    function init($loader) {
        $loader->include('Module_Manager.php');
        
        /* The modules directory of the installation is set before the one in 
         * core so that installation-specific modules can override core
         * modules. */
        $modules_paths = array(
            $loader->aquarius->install_path.'modules'.DIRECTORY_SEPARATOR,
            $loader->aquarius->core_path.'modules'.DIRECTORY_SEPARATOR,
        );

        $loader->aquarius->module_manager = new Module_Manager($modules_paths);

        try {
            $loader->aquarius->modules = $loader->aquarius->module_manager->load_active_modules();
        } catch(No_Such_Module_Exception $e) {
            Log::fail($e);
            Log::warn("Module missing, trying to rescue by updating module list");
            $loader->aquarius->module_manager->update_list($remove_only = true);
            
            // If it fails again we let it fail
            $loader->aquarius->modules = $loader->aquarius->module_manager->load_active_modules();
        }
    }
}


class Aquarius_Stage_logging extends Aquarius_Basic_Stage {
    var $logging_manager;
    
    function init($loader} {
        require_once ('lib/Logging_Manager.php');
        $this->logging_manager = new Logging_Manager(ECHOKEY, $loader->aquarius->conf('log'), $loader->install_path);   
    }

    function load($loader) {
        $logger = $this->logging_manager->load_for(clean_magic($_COOKIE));
        $loader->aquarius->logger = $logger;
        Log::$usuallogger = $logger;
        
        // Display PHP errors and warnings when echolevel is on debug
        if ($loder->aquarius->logger->echolevel < Log::INFO || $loader->aquarius->logger->firelevel < Log::INFO) {
            ini_set('display_errors','1');
        } else {
            // Don't change preset
        }
    }
}

class Aquarius_Stage_globals extends Aquarius_Basic_Stage {
    function load($loader) {
        $GLOBALS['aquarius'] = $loader->aquarius;
        $GLOBALS['DB'] = $loader->db_legacy;
    }
}

    