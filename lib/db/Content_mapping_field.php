<?php
/**
 * Table Definition for content_mapping_field
 */
require_once 'DB/DataObject.php';

class db_Content_mapping_field extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'content_mapping_field';           // table name
    public $content_mapping_id;              // int(11)  not_null multiple_key group_by
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $element;                         // varchar(750)  
    public $value;                           // varchar(750)  
    public $last_change;                     // int(10)  unsigned group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Content_mapping_field',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
