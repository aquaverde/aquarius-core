<?php
/**
 * Table Definition for module_newsletter
 */
require_once 'DB/DataObject.php';

class db_Module_newsletter extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'module_newsletter';               // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $newsletter_name;                 // string(765)  not_null
    public $newsletter_from;                 // string(765)  not_null
    public $newsletter_from_email;           // string(765)  not_null
    public $newsletter_description;          // blob(50331645)  not_null blob
    public $subscribe_warning;               // blob(50331645)  not_null blob
    public $subscribe_error;                 // blob(50331645)  not_null blob
    public $subscribe_thanx;                 // blob(50331645)  not_null blob
    public $subscribe_subject;               // string(765)  not_null
    public $subscribe_text;                  // blob(50331645)  not_null blob
    public $unsubscribe_warning;             // blob(50331645)  not_null blob
    public $unsubscribe_error;               // blob(50331645)  not_null blob
    public $unsubscribe_thanx;               // blob(50331645)  not_null blob
    public $unsubscribe_subject;             // string(765)  not_null
    public $unsubscribe_text;                // blob(50331645)  not_null blob

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}
