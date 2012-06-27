<?
require_once("lib/module.php");
require_once 'lib/db/Modules.php';

/** Instantiate active modules and update the list of available modules
  */
class Module_Manager {
    /** List of filesystem paths to modules
      * When a module has to be loaded, it is loaded from the first directory
      * where it's present. */
    var $modules_paths;
    
    /** List of loaded modules
      * Note that loaded does not mean activated.
      */
    var $modules;

    /** Initialize a module manager
      * @param $modules_paths Look in these directories for modules
      */
    function __construct(array $modules_paths) {
        $this->modules_paths = $modules_paths;
    }

    /** Load a module instance by its short name
      * @param $short short name of the module to load
      * @return loaded module class
      *
      */
    function load_module($short) {
        // check whether that module has been loaded before
        if (isset($this->modules[$short])) {
            return $this->modules[$short];
        }
        
        $module_class_path = false;
        $module_path = false;
        foreach($this->modules_paths as $modules_path) {
            $module_path = $modules_path.$short.DIRECTORY_SEPARATOR;
            $cp = $module_path.$short.'.php';
            if (is_dir($module_path) && is_file($cp)) {
                $module_class_path = $cp;
                break;
            }
        }
        
        if (!class_exists($short, false)) {

            if (!$module_class_path) throw new No_Such_Module_Exception("Unable to find module '$short' in ".join(', ', $this->modules_paths));

            Log::debug("Try loading module class $short");
            $success = include_once $module_class_path;
            if (!$success) throw new No_Such_Module_Exception("Failed including $module_class_path");
        }

        // If there's still no class present, there's nothing we can do
        if (!class_exists($short, false)) {
            throw new No_Such_Module_Exception("No class $short");
        }

        // Ok, everything ready
        $this->modules[$short] = new $short($module_path);
        return $this->modules[$short];
    }

    /** Load all active modules */
    function load_active_modules() {
        $dbmodule = DB_DataObject::factory('modules');
        $dbmodule->active = true;
        $dbmodule->find();

        $modules = array();
        while ($dbmodule->fetch()) {
            $modules[$dbmodule->short] = $this->load_module($dbmodule->short);
        }
        return $modules;
    }


    /** Get a list of available modules
      * This is the module list from the db
      *
      * @param $active=false return only active modules when true
      *
      * @return array of db module information objects (not module instances) indexed by the module short name
      */
    function available_modules($active = false) {
        $dbmodule = DB_DataObject::factory('modules');
        if ($active) $dbmodule->active = true;
        $dbmodule->find();
        $dbmodules = array();
        while($dbmodule->fetch()) {
            $dbmodules[$dbmodule->short] = clone $dbmodule;
        }
        return $dbmodules;
    }
    
    /** Check module path for new modules and remove modules not present anymore from DB
      * This is a risky function because it may execute module code with so far
      * undiscovered errors in new modules.
      *
      * @param $remove_only=false don't add modules to DB, remove the ones that are not present anymore (this is pretty safe)
      *
      */
    function update_list($remove_only = false) {
        $dbmodules = $this->available_modules();

        $fsmodules = array();
        foreach($this->modules_paths as $modules_path) {
            Log::debug("Looking for modules: Checking $modules_path");
            if(is_dir($modules_path) && $dh = opendir($modules_path)) {
                while (($dir = readdir($dh)) !== false) {
                    $module_path = $modules_path.$dir.DIRECTORY_SEPARATOR;
                    if(!is_dir($module_path) || $dir[0] == '.' || $dir == '..') continue;
                    // To be an available module, there must exist a .php file named like the module directory
                    $module_class_path = $module_path.$dir.'.php';
                    Log::debug("Checking $module_class_path");
                    if(file_exists($module_class_path) && !isset($fsmodules[$dir])) {
                        $dbmodule = new db_Modules();
                        $dbmodule->short = $dir;

                        $fsmodules[$dbmodule->short] = $dbmodule;
                        Log::debug("Adding $dbmodule->short to list of available modules");
                    } else {
                        Log::debug("$module_class_path doesn't exist, ignoring.");
                    }
                }
                closedir($dh);
            } else {
                Log::debug("Looking for modules: Unable to open $modules_path");
            }
        }

        // remove modules present in DB only
        foreach(array_diff_key($dbmodules, $fsmodules) as $short => $dbmodule) {
            Log::info("Removing '$short' module settings from DB");
            $dbmodule->delete();
        }

        // Add modules not yet in DB
        if (!$remove_only) {
            foreach(array_diff_key($fsmodules, $dbmodules) as $short => $dbmodule) {
                Log::info("Adding settings for module '$short' to DB");
                $module = $this->load_module($short);
                $dbmodule->active = false;
                $dbmodule->name = strlen($module->name) > 0 ? $module->name : $short;
                $dbmodule->insert();
            }
        }
    }
}

class No_Such_Module_Exception extends Exception {}