<?php
/**
 * Table Definition for shop_order
 */
require_once 'DB/DataObject.php';

class db_Shop_order extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shop_order';                      // table name
    public $id;                              // string(60)  not_null primary_key
    public $sequence_nr;                     // int(11)  not_null
    public $status;                          // string(27)  not_null enum
    public $user_id;                         // int(11)  not_null
    public $completed;                       // int(1)  
    public $paid;                            // int(1)  
    public $pay_date;                        // int(11)  
    public $paymethod_id;                    // int(10)  not_null unsigned
    public $delivermethod_id;                // int(10)  not_null unsigned
    public $discount;                        // int(11)  not_null
    public $total_price;                     // real(22)  not_null
    public $order_date;                      // int(11)  not_null
    public $address_id;                      // int(11)  not_null
    public $comments;                        // blob(196605)  not_null blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Shop_order',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
