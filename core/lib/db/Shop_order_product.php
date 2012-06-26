<?php
/**
 * Table Definition for shop_order_product
 */
require_once 'DB/DataObject.php';

class db_Shop_order_product extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_order_product';              // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $order_id;                        // int(10)  not_null unsigned
    public $product_id;                      // int(10)  not_null unsigned
    public $price;                           // real(22)  not_null
    public $total_price;                     // real(22)  
    public $count;                           // int(11)  
    public $discount;                        // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_order_product',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
