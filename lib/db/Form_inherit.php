<?php
/**
 * Table Definition for form_inherit
 */
require_once 'DB/DataObject.php';

class db_Form_inherit extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'form_inherit';                    // table name
    public $child_id;                        // int(10)  not_null primary_key unsigned group_by
    public $parent_id;                       // int(10)  not_null primary_key unsigned group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Form_inherit',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
