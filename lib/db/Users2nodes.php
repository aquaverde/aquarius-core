<?php
/**
 * Table Definition for users2nodes
 */
 
class db_Users2nodes extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'users2nodes';                     // table name
    public $userId;                          // int(11)  not_null primary_key group_by
    public $nodeId;                          // int(11)  not_null primary_key group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Users2nodes',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

}
