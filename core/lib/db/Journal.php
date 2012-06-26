<?php
/**
 * Table Definition for journal
 */

class db_Journal extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'journal';                         // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $content_id;                      // int(11)  not_null
    public $user_id;                         // int(11)  not_null
    public $last_change;                     // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Journal',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    public static function content_updated($content) {
        $contentid = 0;
        if(!is_numeric($content)) {
            $contentid = $content->id;
        } else {
            $contentid = $content;
        }
        $proto = DB_DataObject::factory('journal');
        $proto->content_id = $contentid;
        $proto->user_id = db_Users::authenticated()->id;
        $proto->last_change = time();
        
        $proto->insert();
    }

    public static function last_update($content) {
        $journal_entry = new self();
        $journal_entry->content_id = $content->id;
        $journal_entry->orderBy('last_change DESC');
        $found = $journal_entry->find(true);
        return $found ? $journal_entry : false;
    }

    function get_user() {
        $user = new db_Users();
        $user->id = $this->user_id;
        if ($user->find(true)) return $user;
        return false;
    }
}
