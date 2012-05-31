<?php
/**
 * Table Definition for superadmin_prefs
 */
require_once 'DB/DataObject.php';

class db_Superadmin_prefs extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'superadmin_prefs';                // table name
    public $superadmin_prefs_id;             // int(10)  not_null primary_key unsigned auto_increment
    public $pref_name;                       // string(750)  not_null
    public $pref_val;                        // string(3)  not_null enum

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Superadmin_prefs',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
