<?php
/**
 * Table Definition for users2modules
 */
class db_Users2modules extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'users2modules';                   // table name
    public $userId;                          // int(11)  not_null primary_key group_by
    public $moduleId;                        // int(11)  not_null primary_key group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Users2modules',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    static function deleteUser($userId) {
        $proto = DB_DataObject::factory('users2modules');
        $proto->userId = $userId;
        $proto->find();
        
        while ( $proto->fetch() )
            $proto->delete();
    }
    
    static function addUsersModule($userId, $mod) {
        $proto =& DB_DataObject::factory('users2modules');
        $proto->userId = $userId;
        $proto->moduleId = $mod;
        $proto->insert();
    }
    
    /** Get the list of available active modules for the given user
     * @return hash of module->id, module->short
     */
    static function getActiveModulesForUser($user) {
        $proto =& DB_DataObject::factory('users2modules');
        $proto->userId = $user->id;
        $found = $proto->find();

        // Map list of modules to id=>short hash
        $result = array();
        while ($proto->fetch()) {
            $module = DB_dataObject::staticGet('db_Modules', $proto->moduleId);
            if($module->active)
                $result[$proto->moduleId] = $module;
        }
        return $result;
    }

    static function module_active_for_user($module_short, $user) {
        $module = db_Modules::get_module($module_short);
        $rel = DB_DataObject::factory('users2modules');
        $rel->userId = $user->id;
        $rel->moduleId = $module->id;
        return $rel->find();
    }
}
