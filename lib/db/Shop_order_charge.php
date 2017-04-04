<?php
/**
 * Table Definition for shop_order_charge
 */
require_once 'DB/DataObject.php';

class db_Shop_order_charge extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_order_charge';               // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $order_id;                        // string(765)  not_null multiple_key
    public $name;                            // string(765)  not_null
    public $value;                           // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_order_charge',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
