<?php
/**
 * Table Definition for fe_address
 */

class db_Fe_address extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fe_address';                      // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $firstname;                       // varchar(750)  not_null
    public $lastname;                        // varchar(750)  not_null
    public $firma;                           // varchar(765)  
    public $address;                         // varchar(750)  not_null
    public $zip;                             // varchar(30)  not_null
    public $city;                            // varchar(750)  not_null
    public $country;                         // varchar(750)  
    public $phone;                           // varchar(150)  
    public $mobile;                          // varchar(150)  
    public $email;                           // varchar(750)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Fe_address',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
