<?php
/**
 * Table Definition for fe_address
 */

class db_Fe_address extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fe_address';                      // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $firstname;                       // string(750)  not_null
    public $lastname;                        // string(750)  not_null
    public $firma;                           // string(765)  
    public $address;                         // string(750)  not_null
    public $zip;                             // string(30)  not_null
    public $city;                            // string(750)  not_null
    public $country;                         // string(750)  
    public $phone;                           // string(150)  
    public $mobile;                          // string(150)  
    public $email;                           // string(750)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Fe_address',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
