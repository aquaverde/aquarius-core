<?php
/**
 * Table Definition for dynform_field
 */
require_once 'DB/DataObject.php';

class db_Dynform_field extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dynform_field';                   // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $block_id;                        // int(10)  not_null unsigned
    public $type;                            // int(11)  not_null
    public $name;                            // blob(50331645)  not_null blob
    public $weight;                          // int(11)  not_null
    public $required;                        // int(1)  not_null
    public $num_lines;                       // int(11)  not_null
    public $width;                           // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Dynform_field',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
