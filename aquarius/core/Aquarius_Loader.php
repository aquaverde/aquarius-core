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

    var $stages_loaded = array();

    var $aquarius;
    var $db;
    
    
    /** Load aquarius stages by name */
    function init() {
        $stages = func_get_args();
        foreach($stages as $stage) {
            if (isset($this->stages_loaded[$stage])) return;
            else if (method_exists($this, $stage)) {
                if (isset($this->stages_loaded['basic_logging']) || isset($this->stages_loaded['configure_logging'])) {
                    Log::debug("Aquarius stage $stage");
                }
                $this->$stage();
                $this->stages_loaded[$stage]= $stage;
            } else {
                throw new Exception("Unknown loader stage '$stage'");
            }
        }
        return $this->aquarius;
    }
    
    
    /** Find filesystem paths based on the location of this file */
    function find_paths() {
        $this->core_path = realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
        $this->root_path = realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR;
    }
    
    
    function set_include_paths() {
        $this->init('find_paths');
        $result = set_include_path($this->core_path.PATH_SEPARATOR.$this->core_path.'pear/'.PATH_SEPARATOR.get_include_path());
        if ($result === false) throw new Exception("Unable to set include path.");
    }
    
    
    function create_aquarius() {
        $this->init('set_include_paths', 'basic_settings');
        require_once("lib/aquarius.php");
        $this->aquarius = new Aquarius($this->root_path, $this->core_path);
        $this->aquarius->load_configs();
    }
    
    function class_autoloader() {
        // Autoload lib and db classes
        spl_autoload_register(array($this->aquarius, 'autoload_class'));
    }
    
    
    /** Collection of some random PHP settings */
    function basic_settings() { 
        $this->init('error_reporting');      
        $aquarius_version = array(3, 6, 5);
        define('AQUARIUS_VERSION', join('.', $aquarius_version));

        // Turn on output buffering, so that later code may change headers
        ob_start();

        // We use UTF8 exclusively
        mb_internal_encoding('UTF-8');

        date_default_timezone_set('UTC');
    }
    
    function configure_settings() {
        date_default_timezone_set($this->aquarius->conf('timezone'));
    }
    
    
    function load_libs() {
        $this->init('set_include_paths');
        // Do we need all this?
        require_once 'lib/url.php';
        require_once 'lib/translation.php';
        require_once 'lib/Formtype.php';
        require_once 'lib/db/Languages.php';
        require_once 'lib/template.lib.php';
    }
    

    function establish_db_connection() {
        require 'lib/sql.lib.php';
        require 'DB/DataObject.php';
        
        // Tell PEAR to throw errors instead of returning obscure error objects.
        // At least we will notice something's awry that way.
        PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'pear_error_to_exception');

        // Setup PEAR DB_DataObject
        $pear_options = &PEAR::getStaticProperty('DB_DataObject','options');
        $dbconf = $this->aquarius->conf('db');
        $pear_options = array(
            'database'         => 'mysqli://'.$dbconf['user'].':'.DB_PASSWORD.'@'.$dbconf['host'].'/'.$dbconf['name'],
        //  'database_global'  => 'mysql://'.GLOBALDB_USERNAME.':'.GLOBALDB_PASSWORD.'@'.DB_HOST.'/'.GLOBALDB_DBNAME,
        //  'schema_location'  => PROJECT_INCLUDE_PATH.'lib/db/',
            'ini_'.$dbconf['name'] => $this->core_path.'lib/db/schema.merged.ini', // Explicit schema location
            'class_location'       => 'lib/db/',
            'class_prefix'         => 'db_',
            'debug'                => PEARLOGLEVEL
        );
        
        // Legacy DB connection
        $this->db = new SQLwrap($dbconf['host'], $dbconf['user'], DB_PASSWORD, $dbconf['name']);
    }
    
    function modules() {
         require_once 'lib/Module_Manager.php';
         
        
        /* The modules directory of the installation is set before the one in 
         * core so that installation-specific modules can override core
         * modules. */
        $modules_paths = array(
            $this->aquarius->install_path.'modules'.DIRECTORY_SEPARATOR,
            $this->aquarius->core_path.'modules'.DIRECTORY_SEPARATOR,
        );

        $this->aquarius->module_manager = new Module_Manager($modules_paths);

        try {
            $this->aquarius->modules = $this->aquarius->module_manager->load_active_modules();
        } catch(No_Such_Module_Exception $e) {
            Log::fail($e);
            Log::warn("Module missing, trying to rescue by updating module list");
            $this->aquarius->module_manager->update_list($remove_only = true);
            
            // If it fails again we let it fail
            $this->aquarius->modules = $this->aquarius->module_manager->load_active_modules();
        }
    }


    /** Load logging class and configure some minimal logging without requiring
      * configuration */
    function basic_logging() {
        $this->init('set_include_paths');
        require_once ('lib/utility.lib.php');
        require_once ('lib/log.php');

        Log::$usuallogger = new Logger(
            $this->core_path.'../cache/log.txt',
            Log::INFO,
            Log::NEVER,
            Log::NEVER
        );        
    }    

    /** Configure logging */
    function configure_logging() {
        require_once ('lib/Logging_Manager.php');
        $this->aquarius->logging_manager = new Logging_Manager(ECHOKEY, $this->aquarius->conf('log'), $this->aquarius->install_path);

        $this->aquarius->logger = $this->aquarius->logging_manager->load_for(clean_magic($_COOKIE));
        Log::$usuallogger = $this->aquarius->logger;
        
        // Display PHP errors and warnings when echolevel is on debug
        if ($this->aquarius->logger->echolevel < Log::INFO || $this->aquarius->logger->firelevel < Log::INFO) {
            ini_set('display_errors','1');
        } else {
            // Don't change preset
        }
    }


    function error_reporting() {   
        $this->init('set_include_paths');
        
        // Make sure we see them errors
        // Unfortunately we can't enable depreciation warnings and strict
        // standard warnings because the PEAR PHP4 compatible classes use
        // call-time pass-by-reference.
        error_reporting(E_ALL & ~E_STRICT & ~8192); // 8192 = E_DEPRECATED, since 5.3.0
    
        // Load includes, just the ones needed in the config & init
        require_once ('lib/utility.lib.php');
        require_once ('lib/log.php');

        // Convince PHP to treat unhandled Exceptions, E_ERROR and E_PARSE like any
        // other error
        set_exception_handler('process_exception');
        if (version_compare(PHP_VERSION, '5.2') >= 0) register_shutdown_function('handle_fatal_errors');
    }
    
    function GLOBALS() {
        $GLOBALS['aquarius'] = $this->aquarius;
        $GLOBALS['DB'] = $this->db;
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
            'GLOBALS'
        );
    }
}

