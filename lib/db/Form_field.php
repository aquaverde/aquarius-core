<?php
/**
 * Table Definition for form_field
 */

class db_Form_field extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'form_field';                      // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $name;                            // varchar(750)  not_null multiple_key
    public $description;                     // varchar(750)  not_null
    public $sup1;                            // int(11)  not_null group_by
    public $sup2;                            // int(11)  not_null group_by
    public $sup3;                            // varchar(750)  not_null
    public $sup4;                            // varchar(750)  not_null
    public $weight;                          // int(10)  not_null multiple_key unsigned group_by
    public $type;                            // varchar(765)  multiple_key
    public $form_id;                         // int(10)  not_null multiple_key unsigned group_by
    public $multi;                           // tinyint(1)  not_null group_by
    public $language_independent;            // tinyint(1)  not_null multiple_key group_by
    public $add_to_title;                    // tinyint(1)  not_null group_by
    public $permission_level;                // int(11)  not_null group_by
    public $inherited;                       // tinyint(3)  not_null unsigned group_by

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
    
    /** build list of differences by field*/
    function diff($other) {
        $diffs = array();
        foreach(array('name', 'description', 'sup1', 'sup2', 'sup3', 'sup4', 'weight', 'type', 'multi', 'language_independent', 'add_to_title', 'permission_level') as $n) {
           if ($this->$n != $other->$n) $diffs[$n] = array($this->$n, $other->$n);
        }
        return $diffs;
    }
}
