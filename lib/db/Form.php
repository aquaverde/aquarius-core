<?php
/** @package Aquarius */

/** Define fields to be used in content. */
class db_Form extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'form';                            // table name
    public $id;                              // int(11)  not_null primary_key unsigned auto_increment group_by
    public $title;                           // varchar(450)  not_null
    public $template;                        // varchar(450)  
    public $sort_by;                         // varchar(90)  
    public $sort_reverse;                    // tinyint(1)  not_null group_by
    public $fall_through;                    // char(24)  not_null
    public $show_in_menu;                    // tinyint(1)  not_null multiple_key group_by
    public $fieldgroup_selection_id;         // int(11)  multiple_key group_by
    public $permission_level;                // int(11)  not_null group_by
    public $content_type;                    // varchar(300)  

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
        $id = $this->id;
        return Cache::call('form_fields'.$id, function() use ($id) {
            $fields = array();
            $formfield_prototype = DB_DataObject::factory('form_field');
            $formfield_prototype->form_id = $id;
            $formfield_prototype->orderBy("weight ASC");
            $formfield_prototype->find();
            while ($formfield_prototype->fetch()) {
                assert("preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\$/', '$formfield_prototype->name'); /* Field name must be a valid variable name */");
                $fields[$formfield_prototype->name] = clone $formfield_prototype;
            }
            return $fields;
    	});
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

    function child_forms() {
        $form_child = DB_DataObject::factory('form_child');
        $form_child->parent_id = $this->id;
        $form_child->orderBy('preset DESC');

        $form_child->find();
        $form_children = array();
        while($form_child->fetch()) {
            $child_form = DB_DataObject::factory('form');
            $found = $child_form->get($form_child->child_id);
            if ($found) $form_children []= $child_form;
        }
        return $form_children;
    }

    function field_parents() {
        $link = DB_DataObject::factory('form_inherit');
        $link->child_id = $this->id;

        $link->find();
        $field_parents = array();
        while($link->fetch()) {
            $field_parent = DB_DataObject::factory('form');
            $found = $field_parent->get($link->parent_id);
            if ($found) $field_parents []= $field_parent;
        }

        return $field_parents;
    }
    
    function field_children() {
        $link = DB_DataObject::factory('form_inherit');
        $link->parent_id = $this->id;

        $link->find();
        $field_children = array();
        while($link->fetch()) {
            $field_child = DB_DataObject::factory('form');
            $found = $field_child->get($link->child_id);
            if ($found) $field_children []= $field_child;
        }

        return $field_children;
    }

    function preset_child() {
        $form_child = DB_DataObject::factory('form_child');
        $form_child->parent_id = $this->id;
        $form_child->preset = 1;
        $found = $form_child->find(true);
        if ($found) {
            return $form_child;
        } else {
            $available = $this->child_forms();
            return array_shift($available);
        }
    }


    /** Develop plan to update inherited fields */
    function plan_reform() {
        $own_fields = $this->get_fields();

        $reps = array($this->id => $this);
        $plans = array();
        foreach($this->field_parents() as $field_parent) {
            $plans []= $field_parent->plan_reform_inherit($this, $own_fields, $reps);
        }

        $plan = Form_Reform_Plan::merge($plans);

        foreach($own_fields as $own_field) {
            if ($own_field->inherited && $plan->unregistered($own_field->name)) {
                $plan->remove($own_field);
            }
        }

        return $plan->flat();
    }

    function plan_reform_inherit($target_form, $present_fields, $reps) {
        $plan = new Form_Reform_Plan($this);
        // Cycle detection

        $cycle = isset($reps[$this->id]);
        $reps[$this->id] = $this;

        if ($cycle) {
            $plan->cycle($reps);
            return $plan;
        }

        $parents_fields = $this->get_fields();
        foreach($parents_fields as $parents_field) {
            if (!$parents_field->inherited) {
                $name = $parents_field->name;
                $inherited_field = clone $parents_field;
                $inherited_field->inherited = true;
                $inherited_field->form_id = $target_form->id;
                if (!isset($present_fields[$name])) {
                    $inherited_field->id = null;
                    $plan->add($inherited_field);
                } else {
                    $existing_field = $present_fields[$name];
                    $inherited_field->id = $existing_field->id;
                    if ($existing_field->inherited) {
                        $diff = $existing_field->diff($parents_field);
                        if ($diff) {
                            // The reference field changed
                            // Update the inherited version
                            $plan->update($inherited_field);
                        } else {
                            $plan->good($existing_field);
                        }
                    } else {
                        // The field has the same name 
                        // Provide update that cancels the override
                        $plan->reset($inherited_field);
                    }
                }
            }
        }

        $plans = array();
        foreach($this->field_parents() as $field_parent) {
            $plans []= $field_parent->plan_reform_inherit($target_form, $present_fields, $reps);
        }

        return Form_Reform_Plan::merge($plans, $plan);
    }


    /** Apply reform plan */
    function reform($plan) {
        Cache::clean(); // shoudln't be here
        foreach($plan['add'] as $field) {
            $field->insert();
        }
        foreach($plan['update'] as $field) {
            $field->update();
        }
        foreach($plan['reset'] as $field) {
            $field->update();
        }
        foreach($plan['remove'] as $field) {
            $field->delete();
        }
    }

    function __toString() {
        return "'".$this->title."' (".$this->id.")";
    }
}

class Form_Reform_Plan {
    var $origin = array();
    var $registered = array();
    var $fields = array(
            'good' => array(),
            'add' => array(),
            'update' => array(),
            'reset' => array(),
            'remove' => array()
        );
    var $conflicting = array();
    var $cycles = array();

    function __construct($form) {
        $this->form = $form;
    }

    // The field needs no alteration
    function good($field, $form = false) {
        if ($this->register($field->name, true, $form)) {
            $this->fields['good'][$field->name] = $field;
        }
    }

    // The field must be added
    function add($field, $form = false) {
        if ($this->register($field->name, true, $form)) {
            $this->fields['add'][$field->name] = $field;
        }
    }

    //The field must be updated
    function update($field, $form = false) {
        if ($this->register($field->name, true, $form)) {
            $this->fields['update'][$field->name] = $field;
        }
    }

    // The field is overridden and can be reset
    function reset($field, $form = false) {
        if ($this->register($field->name, false, $form)) {
            $this->fields['reset'][$field->name] = $field;
        }
    }

    // The field is not present
    function remove($field, $form = false) {
        if ($this->register($field->name, false, $form)) {
            $this->fields['remove'][$field->name] = $field;
        }
    }

    function conflict($name, $new_conflicting) {
        if (!isset($this->conflicting[$name])) {
            $this->conflicting[$name] = array();
        }
        $this->conflicting[$name][$new_conflicting->id] = $new_conflicting;
    }

    function cycle($forms) {
        $this->cycles []= $forms;
    }

    function register($name, $conflict = true, $form = false) {
        if (!$form) $form = $this->form;
        if (!$form) throw new Exception("Cannot register $name because form was not specified");

        if (isset($this->registered[$name])) {
            $seen_in = $this->origin[$name];
            if ($seen_in == $form) return false; // Ignore merges (due to diamond inheritance), cycles are detected separately

            if ($conflict) {
                $this->conflict($name, $seen_in);
                $this->conflict($name, $form);
            }
            return false;
        } else {
            $this->registered[$name] = true;
            $this->origin[$name] = $form;
            return true;
        }
    }

    function unregistered($name) {
        return !isset($this->registered[$name]);
    }

    function flat() {
        $fields = $this->fields;
        $fields['conflicting'] = $this->conflicting;
        $fields['cycles'] = $this->cycles;

        return $fields;
    }

    // Combine plans into a bigger plan
    //
    // Field updates are merged by method with 'good' ones first. This avoids
    // oscillating updates over conflicting fields.
    //
    // If a master plan is provided, the other plans are merged into that one
    // discarding any conflicts they may cause.
    //
    static function merge($plans, $master = false) {
        if ($master) {
            $new = clone $master;
            $masterconflicts = $master->conflicting;
        } else {
            $new = new self(false);
        }

        foreach(array('good', 'reset', 'update', 'add', 'remove') as $method) {
            foreach($plans as $plan) {
                foreach($plan->fields[$method] as $field) {
                    $new->$method($field, $plan->origin[$field->name]);
                }
            }
        }

        if ($master) {
            $new->conflicting = $masterconflicts;
        }

        foreach($plans as $plan) {
            $new->cycles = array_merge($new->cycles, $plan->cycles);
        }

        return $new;
    }
}
