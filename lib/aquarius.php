<?php
/** @package Aquarius */

/** Run time basics for the CMS
  */
class Aquarius {
    /** Server root path
      * This is usually the webroot, '/var/www', 'public_html' or similar. But
      * Aquarius may be installed in subdirectories as well. */
    var $root_path;

    /** The path where aquarius-specific files reside.
      * Usually this is $root_path.'aquarius/'.
      */
    var $install_path;

    /** Path to core files
      * Usually this is $install_path.'core/'
      */
    var $core_path;


    /** Dictionary with config values */
    var $config = array();

    /** Associative list of module's short name => module object*/
    var $modules = array();
    
    var $module_manager;
    
    /** Functions that are called from specific points in the CMS processing.
      * Mostly used by modules
      */
    var $hooks = array();

    var $formtypes = null;

    /** Create an aquarius instance
      *
      * @param $root_path the root of the site managed by aquarius
      * @param $core_path location of the core directory
      *
      * The aquarius installation directory is assumed to be in the parent
      * directory from the core_path.
      *
      */
    function __construct($root_path, $core_path) {
        $this->root_path = $root_path;
        $this->core_path = $core_path;
        $this->install_path = realpath($core_path.'..').DIRECTORY_SEPARATOR;
    }
    
    /** Return the installed aquarius revision as string
      * Currently, the revision string is read from the file 'revision' in the core path. */
    function revision() {
        return @file_get_contents($this->core_path.'revision');
    }

    /** Load the configuration files into $config
      *
      * The following configuration files are loaded:
      * - core_path/config.base.php    Default values for all Aquarius installations
      * - install_path/config.php      Configuration options for website replacing default values
      * - install_path/conf.d/*.php    load configs from these files DEPRECATED
      * - install_path/config.local.php Options specific to this installation
      * - core_path/config.post.php    Default define() statements based on configuration
      * 
      * Note that the main config.php file will be reloaded after loading the
      * modules. This allows modules to load config presets which may be
      * overridden in the main config.
      */
    function load_configs() {
        // all the default values of aquarius
        $this->use_config($this->core_path."config.base.php");
        
        // site specific values that overwrite the defaults
        $this->use_config($this->install_path."config.php");

        /* Load all configs in the conf.d directory */
        $confd_path = $this->install_path."/conf.d/";
        if(file_exists($confd_path)) {
            $dh = opendir($confd_path);
            while (($file = readdir($dh)) !== false) {
                if(is_file($confd_path.$file) && substr($file, -4) == '.php')
                    $this->use_config($confd_path.$file);
            }
            closedir($dh);
        }
        
        // local config values used for stuff that specific to each webserver
        $this->use_config($this->install_path."config.local.php");
        
        // Another core config mostly to define stuff that depends on other
        // configuration files
        $this->use_config($this->core_path."config.post.php");
        
        $this->domain_conf = new DomainConfigs($this->conf('frontend/domains'));
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
            $classpath = 'lib/'.$class_name.'.php';
        }
        if ($classpath) {
            Log::backtrace("Autoloading $classpath");
            // FIXME: syntax errors in included files not shown because of '@'
            $success = @include_once($classpath);
        }
    }

    /** Load aquarius modules
     * 
      * The configuration file of each module, 'modulename/modulename.config.php'
      * is loaded, then the module is initialized. The main configuration is
      * reloaded after loading the preset configs so that module presets can be
      * overridden from the main config.
      */
    function load() {
        require_once("lib/formtypes.php");

        // Load module config presets
        foreach($this->modules as $short => $module) {
            $this->use_config($module->path.$module->short.".config.php");
        }

        // Reload main config to override presets
        $this->use_config($this->install_path."config.php", true);
        
        // Activate modules
        foreach($this->modules as $short => $module) {
            Log::debug("Initializing module $short");
            $module->initialize($this);
        }

        $this->formtypes = new FormTypes($this);

        require_once 'lib/FilterParser.php';
        $this->filterparser = new FilterParser('lib/predicates');
    }

    /** Load a file into the aquarius configuration.
      * @param $config_path   include this config file
      * @param $hide_warnings Suppress PHP warnings when including configs. Optional, preset is false.
      * 
      * Care must be taken not to override settings from configs
      * loaded previously. For example, the line
      *  
      *   $config = array('my_setting' => 'is important');
      * 
      * would clear the whole config and leave only this setting. Instead,
      * the line must be written as
      * 
      *   $config['my_setting'] = 'is important';
      * 
      * which leaves intact other entries in the $config construct.
      */
    function use_config($config_path, $hide_warnings=false) {
        if (file_exists($config_path)) {
            $aquarius = $this;
            $config = $this->config;
            if ($hide_warnings) @include $config_path;
            else                 include $config_path;
            $this->config = $config;
        }
    }

    /** Put together a factory for frontend URI generation */
    function frontend_uri_constructor() {
        $steps = array(
            'relocate_for_parent_fallthrough',
            'use_option_host',
            'parent_fallback_when_no_content'
        );

        if (URL_REWRITE) {
            $steps []= 'path_parts_from_nodes';
            $steps []= 'add_html_suffix';
            $steps []= 'add_lg_prefix';
            $steps []= 'build_path';
        } else {
            $steps []= 'plain_uri';
        }

        $frontend_url = new FrontendURLFactory($steps);
        $frontend_url->host = $this->conf("frontend/domain", $_SERVER['SERVER_NAME']);
        $frontend_url->domain_conf = $this->domain_conf;
        $frontend_url->require_active = true;

        $this->execute_hooks('frontend_extend_uri_factory', $frontend_url);

        return $frontend_url;
    }

    /** Get an aquarius config value
      * @see conf()
      */
    function conf($path, $default = null) {
        return conf($this->config, $path, $default);
    }

    /** Session setup
      * Does nothing if the session is already open.
      * @param $lazy Load only if the user had a session started before (this means that the session is started only if the user has a cookie by that name)
      * @return boolean whether session is active (not reliable before PHP 5.3)
      */
    function session_start($lazy = false) {
        $name = $this->conf('session/name', 'aquarius3_session');

        // Check whether session is active already
        if (session_id()) {
            if (session_name() != $name) Log::warn("Session started with name '".session_name()."', config value is $name");
            return true;
        }

        // Maybe start session
        if (!$lazy || isset($_COOKIE[$name])) {
            session_name($name);
            session_cache_limiter('none'); //  We set our own caching headers THANKYOUVERYMUCH
            if (false !== $save_path=$this->conf('session/save_path', false)) {
                if ($save_path[0] !== DIRECTORY_SEPARATOR) {
                    // Relative from cache path
                    $save_path = $this->cache_path($save_path);
                }
                session_save_path($save_path);
                $set_save_path = session_save_path($save_path);
                if ($set_save_path !== $save_path) Log::debug("Failed setting session path to $save_path, is $set_save_path");
                
                // Make sure session files are garbage collected when the
                // path is changed from the server preset
                ini_set('session.gc_probability', 1);
            }
            
            if (false !== $lifetime=$this->conf('session/lifetime', false)) {
                ini_set("session.gc_maxlifetime", $lifetime);
            }
            return session_start();
        }
        return false;
    }

    /** Get session variable
      * To avoid third-party scripts overwriting aquarius3 session vars, they are stored under 'aquarius3' in the session.
      * @param $name Name of the session var to get
      * @param $default Optional default value (defaults to null)
      * @return The value stored under $name in the session, or $default if there is nothing.
    */
    function session_get($name, $default = null) {
        if (!isset($_SESSION['aquarius3']) || !is_array($_SESSION['aquarius3'])) $_SESSION['aquarius3'] = array();
        if (isset($_SESSION['aquarius3'][$name])) return $_SESSION['aquarius3'][$name];
        else return $default;
    }
    
    /** Set session variable
      * This opens the session if it wasn't open before. 
      * To avoid third-party scripts overwriting aquarius3 session vars, they are stored under 'aquarius3' in the session.
      * @param $name Name of the session var to set
      * @param $value The value to be stored under name
    */
    function session_set($name, $value = null) {
        $this->session_start();
        if (!isset($_SESSION['aquarius3']) || !is_array($_SESSION['aquarius3'])) $_SESSION['aquarius3'] = array();
        $_SESSION['aquarius3'][$name] = $value;
    }


    /** Register a hook function.
      * $event is a string describing the occasion when $handler should be called by the CMS.
      * $handler is either an object or a callback type (see http://www.php.net/manual/en/language.pseudo-types.php#language.types.callback )
      * If the handler is an object, the method named like the hook event will be called.
      */
    function register_hook($event, $handler) {
        if (!isset($this->hooks[$event])) $this->hooks[$event] = array();
        $this->hooks[$event][] = $handler;
    }

    /** Execute all handlers for a given event.
      * The first argument is the name of the event, all remaining arguments are passed to the hook functions.
      * If a hook handler is an object, its method named like the event will be executed.
      * Example:
      *   class Test { function menu_init($menu) { $menu->addEntry("Test", false) } }
      *   $aquarius->register_hook('menu_init', new Test());
      *   ... later ...
      *   $aquarius->execute_hooks('menu_init', $my_menu);
      *
      * Returns list of values returned by hook functions, null return values are not included in list.
      *
      * Currently, the execution order of the handlers is not specified.
      */
    function execute_hooks() {
        require_once("lib/module.php");
        $args = func_get_args();
        $event = array_shift($args);
        $results = array();
        if (strlen($event) < 1) throw new Exception("Invalid event argument");
        if (isset($this->hooks[$event])) {
            Log::debug("Executing ".count($this->hooks[$event])." hooks for $event");
            foreach($this->hooks[$event] as $handler) {
                $result = null;
                if (is_object($handler)) {
                    // Call handler's method named $event
                    $result = call_user_func_array(array($handler, $event), $args); 
                } else {
                    // Must be a callback type then
                    $result = call_user_func_array($handler, $args);
                }
                if ($result !== null) $results[] = $result;
            }
        }
        return $results;
    }

    /** Prepare a smarty container */
    function get_smarty_container() {
        require_once('lib/smarty/Smarty.class.php');
        $smarty = new Smarty();
        $smarty->assign('config', $this->config);

        // Add general smarty plugins path
        array_unshift($smarty->plugins_dir, $this->core_path.'lib/smarty_plugins/');

        $smarty->register_function('resize', 'smarty_function_resize');
        $smarty->register_modifier('alt', 'smarty_modifier_alt');
        $smarty->register_modifier('th', 'smarty_modifier_th');
        
        // Let the modules configure the container as well
        $this->execute_hooks('smarty_config', $smarty);

        return $smarty;
    }

    /** Prepare a smarty backend container
      *
      * @param $admin_lg The backend-language to use. This determines the
      *                  language used in the interface, mainly the menu
      *                  translation
      * 
      * If the admin_lg parameter is omitted, the global variable $admin_lg is
      * read. This behaviour is DEPRECATED.
      *
      */
    function get_smarty_backend_container($admin_lg = false) {
        // Legacy hack when $admin_lg is not given
        if (!$admin_lg) $admin_lg = $GLOBALS['admin_lg'];
        
        $smarty = $this->get_smarty_container();
        
        $smarty->template_dir = array($this->core_path.'templates/');
        $smarty->cache_dir    = $this->cache_path('smarty_backend/cache/');
        $smarty->compile_dir  = $this->cache_path('smarty_backend/compile/');
        
        array_unshift($smarty->plugins_dir, $this->core_path.'lib/smarty_backend_plugins/');

        // Adjust caching
        $smarty->force_compile = false;
        $smarty->caching = false; // Never cache for backend
        
        // Load the config containing the admin language translation
        $smarty->config_booleanize = false;
        $smarty->config_load($this->core_path."lang/".$admin_lg.'.lang');

        // Let the modules add configs
        $this->execute_hooks('smarty_config_backend', $smarty, $admin_lg);

        return $smarty;
    }

    /** Prepare smarty frontend container
      * @param lg language to use
      * @param node optional node to load within the template */
    function get_smarty_frontend_container($lg, $node = false) {
        // Legacy hack when $lg is not given
        if (!$lg) $lg = $GLOBALS['lg'];
    
        $smarty = $this->get_smarty_container();
        
        // Content must be active to be shown. This may be overridden by preview mode.
        $smarty->require_active = true;

        $smarty->template_dir = array($this->install_path.'templates/');
        $smarty->compile_dir  = $this->cache_path('smarty_frontend/compile/'); 
        $smarty->cache_dir    = $this->cache_path('smarty_frontend/cache/');
    
        // Put our plugin dirs in front of the internal smarty dir, so we can override smarty plugins
        array_unshift($smarty->plugins_dir, $this->core_path.'lib/smarty_frontend_plugins/');
        array_unshift($smarty->plugins_dir, $this->install_path.'templates/smarty_plugins/'); // Site-specific plugins

        // Prefilter for wording tags
        $smarty->load_filter('pre', 'wording');

		// Outputfilter replace intern Aqualinks generated by RTE
		$smarty->load_filter('output', 'replace_aqualink');

        $cache_settings = $this->conf('frontend/cache');
        $smarty->caching = (bool)$cache_settings['templates'];
        $smarty->cache_lifetime = intval($cache_settings['lifetime']);
        // maybe force smarty to recompile tepmlates on every execution.
        $smarty->force_compile = !(bool)$cache_settings['compiles'];

        // register a function to avoid caching for template blocks
        // use: {dynamic} part of the template which should not be cached {/dynamic}
        $smarty->register_block('dynamic', 'smarty_block_dynamic', false);

        // Register filters for template inheritance blocks
        $smarty->load_filter('post', 'block_fix');
        $smarty->load_filter('pre', 'extends');
        $smarty->_blocks = array(array()); // Blocks are stored here

        // Let the modules add configs
        $this->execute_hooks('smarty_config_frontend', $smarty, $lg);

        $smarty->assign('lg', $lg);
        $lang_conf = $this->install_path."/templates/lang/$lg.lang";
        if (file_exists($lang_conf)) $smarty->config_load($lang_conf);

        $smarty->uri = $this->frontend_uri_constructor();
        $smarty->uri->lg = $lg;

        if ($node) {
            $form = $node->get_form();
            $content = $node->get_content($lg);
            $smarty->assign('node', $node);
            $smarty->assign('form', $form);
            $smarty->assign('content', $content);

            // Directly assign content fields
            if ($content) {
                $smarty->assign($content->get_fields());
            }
        }
        return $smarty;
    }

    function get_formtypes() {
        return $this->formtypes;
    }
    
    /** Get a caching path
      * @param subdir Optional, append this subdir. Create it if it doesn't exist.
      * @return Absolute path to cache directory, including trailing slash. */
    function cache_path($subdir=null) {
        $cache_path = $this->install_path.'cache';
        if (strlen($subdir) > 0) $cache_path .= '/'.$subdir;
        if (!is_dir($cache_path)) {
            or_die(mkdir($cache_path, 0700, true), "Unable to create cache dir $cache_path");
        }
        return realpath($cache_path).'/';
    }
    
    /** Returns true when debugging information should be output */
    function debug() {
       return  $this->logger && $this->logger->echolevel < Log::INFO;
    }
}
