<?php
/**
 * Table Definition for dynform_entry_data
 */
require_once 'DB/DataObject.php';

class db_Dynform_entry_data extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dynform_entry_data';              // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $entry_id;                        // int(11)  not_null
    public $field_id;                        // int(11)  not_null
    public $name;                            // blob(50331645)  not_null blob
    public $value;                           // blob(4294967295)  not_null blob

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
