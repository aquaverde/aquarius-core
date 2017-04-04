<?php
/**
 * Table Definition for shop_node_attribute_settings_seq
 */
require_once 'DB/DataObject.php';

class db_Shop_node_attribute_settings_seq extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_node_attribute_settings_seq';    // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_node_attribute_settings_seq',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
