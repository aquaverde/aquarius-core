<?php
/**
 * Table Definition for dynform_field_type
 */
require_once 'DB/DataObject.php';

class db_Dynform_field_type extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dynform_field_type';              // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $name;                            // string(765)  not_null
    public $default_template;                // string(765)  not_null
    public $template;                        // string(765)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Dynform_field_type',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
