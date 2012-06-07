<?php
/** @package Aquarius */

/** Define fields to be used in content. */
class db_Form extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'form';                            // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment
    public $title;                           // string(450)  not_null
    public $template;                        // string(450)  
    public $sort_by;                         // string(90)  
    public $sort_reverse;                    // int(1)  not_null
    public $fall_through;                    // string(24)  not_null enum
    public $show_in_menu;                    // int(1)  not_null multiple_key
    public $fieldgroup_selection_id;         // int(11)  multiple_key
    public $permission_level;                // int(11)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Form',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
    
    // Tell the FormBuilder to use the title in dropdown selects for forms
    var $fb_linkDisplayFields = array('title');

    /** Get all forms in a list, sorted by title */
    static function get_all() {
        $forms = array();
        $form_prototype = DB_DataObject::factory('form');
        $form_prototype->orderBy("title ASC");
        $form_prototype->find();
        while ($form_prototype->fetch()) {
            $forms[] = clone $form_prototype;
        }
        return $forms;
    }
    
    // Get all forms with spezified permission
    static function get_forms_by_permission($permission_level) {
        $forms = array();
        $form_prototype = DB_DataObject::factory('form');
        $form_prototype->orderBy("title ASC");
        $form_prototype->whereAdd('permission_level >= '.$permission_level);
        $form_prototype->find();
        while ($form_prototype->fetch()) {
            $forms[] = clone $form_prototype;
        }
        return $forms;
    }
    
    /** get all form fields in a list indexed by their id */
    function get_fields() {
        if (!isset($this->fields)) { // Caching
            $this->fields = array();
            $formfield_prototype = DB_DataObject::factory('form_field');
            $formfield_prototype->form_id = $this->id;
            $formfield_prototype->orderBy("weight ASC");
            $formfield_prototype->find();
            while ($formfield_prototype->fetch()) {
                assert("preg_match('^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\$', '$formfield_prototype->name'); /* Field name must be a valid variable name */");           
                $this->fields[$formfield_prototype->name] = clone $formfield_prototype;
            }
        }
    	return $this->fields;
    }
    
    /** Delete this form and all attached form data */
    function delete() {
        // Delete form data
        $child = DB_DataObject::factory('form_field');
        $child->form_id = $this->id;
        $child->find();
        while ($child->fetch()) {
            $child->delete();
        }   
        parent::delete(); // Call the DB_DataObject delete method
    }
    
    /** Clone a form */
    function duplicate($newtitle) {
        $newform = clone $this;
        $newform->title = $newtitle;
        $newid = $newform->insert();
        $child = DB_DataObject::factory('form_field');
        $child->form_id = $this->id;
        $child->find();
        while ($child->fetch()) {
            $newchild = clone $child;
            $newchild->form_id = $newid;
            $newchild->insert();
        }
    }
}
