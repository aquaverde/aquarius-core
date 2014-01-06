<?php
/**
 * Table Definition for content_search
 */

class db_Content_search extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'content_search';                  // table name
    public $id;                              // int(11)  not_null primary_key auto_increment group_by
    public $lg;                              // varchar(2)  not_null
    public $query;                           // varchar(250)  not_null multiple_key
    public $time;                            // timestamp(19)  not_null unsigned zerofill timestamp

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Content_search',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
