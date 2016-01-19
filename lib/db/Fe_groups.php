<?php
/**
 * Table Definition for fe_groups
 */

class db_Fe_groups extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fe_groups';                       // table name
    public $id;                              // int(11)  not_null primary_key auto_increment group_by
    public $name;                            // varchar(300)  not_null
    public $active;                          // tinyint(1)  not_null group_by

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	
	/* function is used to genereate the flag picture link:
	   flag_on 
	   flag_off
	*/
	function getActiveState() {
		if ( $this->active )
			return "on";
		else 
			return "off";
	}

    /** List of all groups
      * Returns active groups only if $require_active is true */
	function getAllGroups($require_active = false) {
		$proto = DB_DataObject::factory('fe_groups');
        if ($require_active) {
            $proto->active = 1;
        }
		$proto->find();
		
		$groups = array();
		
		while ( $proto->fetch() ) $groups[] = clone($proto);
		
		return $groups;
	}
	
	/** Returns true if this group gives access to a node */
	function permitsAccessTo($nodeid) {
	    $proto = DB_DataObject::factory('fe_restrictions');
	    $proto->group_id = $this->id;
	    $proto->node_id = $nodeid;
	    return (bool)$proto->find();
	}
 
    /** Delete this group and all user realations (DB_DataObject::delete override)*/
    function delete($useWhere=false) {
        if ($useWhere) throw new Exception("not supported");
        require_once("Fe_groups2user.php");
        db_Fe_groups2user::removeUsersByGroup($this->id);
        parent::delete(false); // Call the DB_DataObject delete method
    }   
}
