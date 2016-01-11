<?php
/**
 * Table Definition for dynform_field_data
 */
require_once 'DB/DataObject.php';

class db_Dynform_field_data extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dynform_field_data';              // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $field_id;                        // int(10)  not_null unsigned
    public $lg;                              // string(6)  not_null
    public $name;                            // blob(50331645)  not_null blob
    public $options;                         // blob(4294967295)  not_null blob

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
