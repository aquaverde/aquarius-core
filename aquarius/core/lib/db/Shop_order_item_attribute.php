<?php
/**
 * Table Definition for shop_order_item_attribute
 */
require_once 'DB/DataObject.php';

class db_Shop_order_item_attribute extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_order_item_attribute';       // table name
    public $shop_order_product_id;           // int(11)  not_null primary_key
    public $attribute_name;                  // string(750)  not_null primary_key
    public $value;                           // string(750)  not_null
    public $attribute_id;                    // int(10)  unsigned
    public $property_id;                     // int(10)  unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_order_item_attribute',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
