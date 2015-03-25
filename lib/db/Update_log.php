<?php
/**
 * Table Definition for update_log
 */
require_once 'DB/DataObject.php';

class db_Update_log extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'update_log';                      // table name
    public $id;                              // int(11)  not_null primary_key auto_increment group_by
    public $date;                            // int(10)  not_null unsigned group_by
    public $name;                            // blob(196605)  not_null blob
    public $module;                          // blob(196605)  not_null blob
    public $success;                         // tinyint(4)  group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Update_log',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
