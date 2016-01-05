<?php
/**
 * Table Definition for form_child
 */
require_once 'DB/DataObject.php';

class db_Form_child extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'form_child';                      // table name
    public $id;                              // int(11)  not_null primary_key auto_increment group_by
    public $parent_id;                       // int(11)  not_null group_by
    public $child_id;                        // int(11)  not_null group_by
    public $preset;                          // tinyint(3)  not_null unsigned group_by

    /* Static get */
    static function staticGet($k,$v=NULL, $dummy=NULL) { return DB_DataObject::staticGet('db_Form_child',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
