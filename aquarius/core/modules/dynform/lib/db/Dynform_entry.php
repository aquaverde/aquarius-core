<?php
/**
 * Table Definition for dynform_entry
 */
require_once 'DB/DataObject.php';

class db_Dynform_entry extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dynform_entry';                   // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $dynform_id;                      // int(11)  not_null
    public $lg;                              // string(6)  not_null
    public $time;                            // datetime(19)  not_null binary
    public $submitnodetitle;                 // blob(50331645)  not_null blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Dynform_entry',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
