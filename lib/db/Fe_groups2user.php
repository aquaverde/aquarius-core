<?php
/**
 * Table Definition for fe_groups2user
 */

class db_Fe_groups2user extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fe_groups2user';                  // table name
    public $user_id;                         // int(11)  not_null primary_key group_by
    public $group_id;                        // int(11)  not_null primary_key group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Fe_groups2user',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	
	/* 
		get all registered groups for a given userId.
		returns an associative array with the group ids
		as keys AND values.
	*/
	function getGroupsByUser($userId) {
		$groups = array();
		
		$proto = new db_Fe_groups2user();
		$proto->user_id = $userId;
		$proto->find();
		
		while ( $proto->fetch() )
			$groups[$proto->group_id] = $proto->group_id;
			
		return $groups;
		
	}
	
	/* store groups2user relations, based on the userid
	   and an array of group ids
	*/
	function storeGroups2User($userId, $groupsArray) {
		// remove existing relations
		$this->removeGroupsByUser($userId);
		// init new prototype
		$proto = new db_Fe_groups2user();
		$proto->user_id = $userId;
		
		if ( is_array($groupsArray) ) {
			foreach ( $groupsArray as $nextGroup ) {
				$proto->group_id = $nextGroup;
				$proto->insert();
			}
		}
	}
	
	/* deletes all relations to a given user */
	function removeGroupsByUser($userId) {
		$proto = new db_Fe_groups2user();
		$proto->user_id = $userId;
		$proto->delete();
	}
    
    /* deletes all relations to a given group */
    function removeUsersByGroup($groupId) {
        $proto = new db_Fe_groups2user();
        $proto->group_id = $groupId;
        $proto->delete();
    }
}
