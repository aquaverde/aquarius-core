<?php
/**
 * Table Definition for form_field
 */

class db_Form_field extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'form_field';                      // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment
    public $name;                            // string(750)  not_null multiple_key
    public $description;                     // string(750)  not_null
    public $sup1;                            // int(11)  not_null
    public $sup2;                            // int(11)  not_null
    public $sup3;                            // string(750)  not_null
    public $sup4;                            // string(750)  not_null
    public $weight;                          // int(10)  not_null multiple_key unsigned
    public $type;                            // string(765)  multiple_key
    public $form_id;                         // int(10)  not_null multiple_key unsigned
    public $multi;                           // int(1)  not_null
    public $language_independent;            // int(1)  not_null multiple_key
    public $add_to_title;                    // int(1)  not_null
    public $permission_level;                // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Form_field',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    function get_formtype() {
        global $aquarius;
        return $aquarius->get_formtypes()->get_formtype($this->type);
    }

    function get_form() {
        global $aquarius;
        return DB_DataObject::staticGet('db_Form', $this->form_id);
    }
}
