<?php
/**
 * Table Definition for fe_user_address
 */

class db_Fe_user_address extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fe_user_address';                 // table name
    public $fe_user_id;                      // int(11)  not_null primary_key
    public $fe_address_id;                   // int(11)  not_null primary_key

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Fe_user_address',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
