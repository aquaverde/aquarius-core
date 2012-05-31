<?php
/**
 * Table Definition for dynform_settings
 */
require_once 'DB/DataObject.php';

class db_Dynform_settings extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'dynform_settings';                // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $keyword;                         // string(768)  not_null
    public $value;                           // blob(196605)  not_null blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Dynform_settings',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
