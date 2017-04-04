<?php
/**
 * Table Definition for shop_order_attribute
 */
require_once 'DB/DataObject.php';

class db_Shop_order_attribute extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_order_attribute';            // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $order_product_id;                // int(10)  not_null unsigned
    public $type_id;                         // int(10)  not_null unsigned
    public $value;                           // string(90)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_order_attribute',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
