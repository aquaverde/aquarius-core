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
    public $mapping_id;                      // int(11)  not_null multiple_key
    public $attribute_id;                    // int(11)  not_null
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $lg;                              // string(6)  not_null
    public $active;                          // int(1)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Content_mapping',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
