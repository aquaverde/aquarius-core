<?php
/**
 * Table Definition for shop_node_attribute_settings
 */
require_once 'DB/DataObject.php';

class db_Shop_node_attribute_settings extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_node_attribute_settings';    // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $node_id;                         // int(11)  not_null multiple_key
    public $property_node_id;                // int(11)  not_null multiple_key
    public $active;                          // int(1)  not_null multiple_key
    public $picture;                         // string(600)  
    public $price;                           // string(600)  
    public $text;                            // blob(196605)  blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_node_attribute_settings',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
