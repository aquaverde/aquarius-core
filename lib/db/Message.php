<?php
/**
 * Table Definition for message
 */
require_once 'DB/DataObject.php';

class db_Message extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'message';                         // table name
    public $message_id;                      // int(11)  not_null primary_key auto_increment group_by
    public $text;                            // blob(196605)  not_null blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Message',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function get_title() {
        $title = strip_tags(str_replace("\r", '', str_replace("\n", '', $this->text)));
        if (strlen($title) > 40) {
            $title = substr($title, 0, 35).'…';
        }
        return $title;
    }
}
