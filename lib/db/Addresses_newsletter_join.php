<?php
/**
 * Table Definition for addresses_newsletter_join
 */

class db_Addresses_newsletter_join extends DB_DataObject {
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'addresses_newsletter_join';       // table name
    public $newsletter_id;                   // int(11)  not_null
    public $address_id;                      // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Addresses_newsletter_join',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
