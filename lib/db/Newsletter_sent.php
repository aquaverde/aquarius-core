<?php
/**
 * Table Definition for newsletter_sent
 */
require_once 'DB/DataObject.php';

class db_Newsletter_sent extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'newsletter_sent';                 // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $address_id;                      // int(11)  not_null
    public $edition_id;                      // int(11)  not_null
    public $sent;                            // int(1)  not_null
    public $lang;                            // string(6)  not_null

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
