<?php
/**
 * Table Definition for newsletter_subscription
 */
require_once 'DB/DataObject.php';

class db_Newsletter_subscription extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'newsletter_subscription';         // table name
    public $newsletter_id;                   // int(11)  not_null
    public $address_id;                      // int(11)  not_null
    public $subscription_date;               // timestamp(19)  not_null unsigned zerofill binary timestamp
    public $active;                          // int(1)  not_null
    public $activation_code;                 // string(96)  

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
