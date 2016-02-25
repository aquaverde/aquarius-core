<?php
/**
 * Table Definition for shorturi
 */
require_once 'DB/DataObject.php';

class db_Shorturi extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'shorturi';                        // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $domain;                          // string(765)  not_null binary
    public $keyword;                         // string(180)  not_null multiple_key binary
    public $redirect;                        // string(765)  not_null binary

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
