<?php
/**
 * Table Definition for content_search
 */

class db_Content_search extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'content_search';                  // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $lg;                              // string(6)  not_null
    public $query;                           // string(750)  not_null multiple_key
    public $time;                            // timestamp(19)  not_null unsigned zerofill binary timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Content_search',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
