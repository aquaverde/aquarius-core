<?php
/**
 * Table Definition for newsletter_addresses
 */
require_once 'DB/DataObject.php';

class db_Newsletter_addresses extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'newsletter_addresses';            // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $address;                         // string(765)  not_null unique_key
    public $language;                        // string(9)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Newsletter_addresses',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    public $subscriptions = array();
}
