<?php
/**
 * Table Definition for comment
 */
require_once 'DB/DataObject.php';

class db_Comment extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'comment';                         // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $node_id;                         // int(10)  not_null multiple_key unsigned
    public $date;                            // int(10)  not_null multiple_key unsigned
    public $prename;                         // blob(196605)  not_null blob
    public $name;                            // blob(196605)  not_null blob
    public $email;                           // blob(196605)  not_null blob
    public $subject;                         // blob(196605)  not_null blob
    public $body;                            // blob(196605)  not_null blob
    public $status;                          // string(24)  not_null multiple_key enum

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Comment',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
