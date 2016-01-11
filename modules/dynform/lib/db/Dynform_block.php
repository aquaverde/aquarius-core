<?php
/**
 * Table Definition for dynform_block
 */
require_once 'DB/DataObject.php';

class db_Dynform_block extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dynform_block';                   // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $dynform_id;                      // int(10)  not_null unsigned
    public $name;                            // string(765)  not_null
    public $weight;                          // int(11)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
