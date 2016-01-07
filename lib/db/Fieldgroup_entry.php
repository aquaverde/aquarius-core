<?php
/**
 * Table Definition for fieldgroup_entry
 */
require_once 'DB/DataObject.php';

class db_Fieldgroup_entry extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fieldgroup_entry';                // table name
    public $fieldgroup_entry_id;             // int(11)  not_null primary_key auto_increment group_by
    public $fieldgroup_id;                   // int(11)  not_null group_by
    public $selector;                        // varchar(765)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
