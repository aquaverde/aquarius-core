<?php
/**
 * Table Definition for modules
 */
require_once 'DB/DataObject.php';

class db_Modules extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'modules';                         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment group_by
    public $short;                           // varchar(750)  not_null
    public $name;                            // varchar(750)  not_null
    public $active;                          // tinyint(1)  not_null group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Modules',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	
	/** List of all module instances
      * @param $require_active=false Set this to true if you want to load active modules only
      */
	static function getModules($require_active = false) {
		$result = array();
		$modules_proto = DB_DataObject::factory('modules');
  
        if ( $require_active ) $modules_proto->active = true;
		$modules_proto->find();
		
		while ( $modules_proto->fetch() ) {
			$result[$modules_proto->short] = clone($modules_proto);
           
		}           
		return $result;
	}


    
    static function exists($modname) {
        $modules = self::getModules();
        foreach($modules as $mod) {
            if($mod->short == $modname) {
                return true;
            }
        }
        return false;
    }
        
    static function active($modname) {
        $mod = db_DataObject::factory('modules');
        $mod->short = $modname;
        $mod->find(true);
        return $mod->active;
    }

    /** Get db entry for module by short name */
    static function get_module($short) {
        $module = DB_DataObject::factory('modules');
        $module->short = $short;
        $found = $module->find();
        switch ($found) {
            case 0: throw new Exception("No module entry in DB for short '$short'");
            case 1:
                $module->fetch();
                return $module;
            default: throw new Exception("More than one module with short name '$short'");
        }
    }
}
