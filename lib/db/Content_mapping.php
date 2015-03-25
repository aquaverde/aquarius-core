<?php
/**
 * Table Definition for content_mapping
 */
require_once 'DB/DataObject.php';

class db_Content_mapping extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'content_mapping';                 // table name
    public $mapping_id;                      // int(11)  not_null multiple_key group_by
    public $attribute_id;                    // int(11)  not_null group_by
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $lg;                              // char(6)  not_null
    public $active;                          // tinyint(1)  not_null group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Content_mapping',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
