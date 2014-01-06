<?php
/**
 * Table Definition for shop_order_charges
 */
require_once 'DB/DataObject.php';

class db_Shop_order_charges extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_order_charges';              // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $order_id;                        // int(10)  not_null unsigned
    public $name;                            // string(60)  not_null
    public $value;                           // real(22)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_order_charges',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
