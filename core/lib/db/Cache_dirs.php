<?php
/**
 * Table Definition for cache_dirs
 */
require_once 'DB/DataObject.php';

class db_Cache_dirs extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'cache_dirs';                      // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $path;                            // blob(196605)  not_null blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Cache_dirs',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
