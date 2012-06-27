<?php
/**
 * Table Definition for shop_order_item
 */
require_once 'DB/DataObject.php';

class db_Shop_order_item extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_order_item';                 // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $order_id;                        // string(60)  not_null
    public $title;                           // string(750)  not_null
    public $code;                            // string(750)  not_null
    public $count;                           // int(11)  not_null
    public $price;                           // int(11)  not_null
    public $product_id;                      // int(10)  unsigned

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_order_item',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
