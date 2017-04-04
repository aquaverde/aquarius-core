<?php
/**
 * Table Definition for fieldgroup_selection_entry
 */
require_once 'DB/DataObject.php';

class db_Fieldgroup_selection_entry extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fieldgroup_selection_entry';      // table name
    public $fieldgroup_selection_entry_id;    // int(11)  not_null primary_key auto_increment group_by
    public $fieldgroup_selection_id;         // int(11)  not_null multiple_key group_by
    public $fieldgroup_id;                   // int(11)  not_null group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Fieldgroup_selection_entry',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
