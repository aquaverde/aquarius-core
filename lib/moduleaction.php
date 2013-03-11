<?php 
require_once("lib/db/Users2modules.php");
require_once("lib/adminaction.php");


/** Parent class with convenience functions for actions supplied by modules */
class ModuleAction extends AdminAction {
    /** Name of the module the action belongs to. */
	var $modname = false;

    /** Reference to module if modname is set */
    var $module;

    function init($aquarius) {
        $this->module = get($aquarius->modules, $this->modname);
        return parent::init($aquarius);
    }

    /** Check that users have access to the module this action belongs to */
    function permit_user($user) {
        // Require logged-in user
        if (!(bool)$user) return false;
    
        // Forbid executing actions of inactive modules
        if(!db_Modules::active($this->modname)) return false;

        // Determine user's access
        $access = false;

        // Siteadmins may access any active module
        if ($user->isSiteadmin() ) {
            $access = true;
        } else {
            $access = db_Users2modules::module_active_for_user($this->modname, $user);
        }

        return $access && $this->valid($user);
    }

    /** Check that action is valid for given user
      * Permits superadmins only, override this method to change that. */
    function valid($user) {
        return $user->isSuperadmin();
    }

    /** Return module this action belongs to. DEPRECATED
      * Use $this->module instead. */
    function get_module() {
        if (!$this->module) throw new Exception("No module for name $this->modname");
        return $this->module;
    }
}
?>