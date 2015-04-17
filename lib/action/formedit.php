<?php
/* Edit form has the commands
    edit: shows a form to edit the form (sic)
    save: saves changes to the form
*/

class action_formedit extends AdminAction {
    var $props = array("class", "command", "id");

    /** permits superadmin */
    function permit_user($user) {
      return $user->isSuperadmin();
    }

    function load_form() {
        $form = DB_DataObject::factory('form');
        $form->id = $this->id;
        if ($form->id !== 'new') {
            $found = $form->get($this->id);
            if (!$found) throw new Exception("Couldn't load form with id $this->id");
        }
        return $form;
    }

    function reform($form, $reset, $result) {
        $plan = $form->plan_reform();

        if ($plan['cycles']) {
            // Warn about a dumb idea
            $message = new AdminMessage('warn');
            foreach($plan['cycles'] as $cycle) {
                $message->add_html("Forms ".join(', ', $cycle)." are parts of a cycle and inherit their own fields. Their fields will not be updated until the cycle is broken");
            }
            $result->add_message($message);

            return;
        }

        $suppressed_reset = false;
        if (!$reset) {
            $suppressed_reset = $plan['reset'];
            $plan['reset'] = array();
        }
        $form->reform($plan);

        $message = new AdminMessage('info');
        $message->add_html("Form $form");
        $show = false;
        if ($plan['add']) {
            $message->add_html("Inherited ".count($plan['add'])." fields: ".join(', ', array_keys($plan['add'])));
            $show = true;
        }
        if ($plan['update']) {
            $message->add_html("Updated ".count($plan['update'])." fields: ".join(', ', array_keys($plan['update'])));
            $show = true;
        }
        if ($plan['reset']) {
            $message->add_html("Reset ".count($plan['reset'])." fields: ".join(', ', array_keys($plan['reset'])));
            $show = true;
        }
        if ($suppressed_reset) {
            $message->add_html(count($suppressed_reset)." fields currently overridden: ".join(', ', array_keys($suppressed_reset)));
            $show = true;
        }
        if ($plan['remove']) {
            $message->add_html("Removed ".count($plan['remove'])." fields: ".join(', ', array_keys($plan['remove'])));
            $show = true;
        }

        if ($show) $result->add_message($message);

        if ($plan['conflicting']) {
            $message = new AdminMessage('warn');
            $message->add_html("Form $form");
            foreach($plan['conflicting'] as $fieldname => $providers) {
                $message->add_html("Field '$fieldname' is supplied by forms ".join(" and ", $providers));
            }
            $result->add_message($message);
        }

        foreach($form->field_children() as $field_child) {
            // Reset is not applied recursively
            $this->reform($field_child, false, $result);
        }
    }
}


class action_formedit_edit extends action_formedit implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $form = $this->load_form();
        $smarty->assign('form', $form);

        // List of forms to inherit fields from
        $forms_inherited = $aquarius->db->queryhash("
            SELECT f.id, f.title, template
            FROM form f
            JOIN form_inherit fc ON f.id = fc.parent_id AND fc.child_id = ?
            ORDER BY f.title
        ", array($form->id));
        $smarty->assign('forms_inherited', $forms_inherited);

        // Load all form fields
        $fields = $form->get_fields();
        
        foreach($forms_inherited as $form_inherited) {
            $iform = DB_DataObject::factory('form');
            $found = $iform->get($form_inherited['id']);
            if (!$found) throw new Exception("Unable to load inherited form ".$form_inherited['id']);
            
            foreach($iform->get_fields() as $field) {
                $inherited_field = get($fields, $field->name);
                if ($inherited_field && !$inherited_field->inherited) {
                    $inherited_field->override = true;
                }
            }
        }

        // Add ten empty fields at the end
        for($i = 0; $i < 5; $i++) {
            $newfield = DB_DataObject::factory('form_field');
            $newfield->id = 'new'.$i;
            $newfield->new = true;
            $newfield->permission_level=2;
            $fields[] =  $newfield;
        }
        $smarty->assign('fields', $fields);

        // Prepare fallthrough options
        $opts = array('none' => '-none-', 'all' => 'all', 'category' => 'category', 'box' => 'box', 'parent' => 'parent');
        $smarty->assign('fallthroughoptions', $opts);

        // Prepare fieldgrouping selection
        $selection = DB_DataObject::factory('fieldgroup_selection');
        $selection->find();
        $selections = array(0 => '-none-');
        while($selection->fetch()) {
            $selections[$selection->fieldgroup_selection_id] = $selection->name;
        }
        $smarty->assign('fieldgroup_selections', $selections);

        $smarty->assign('form_children', $aquarius->db->queryhash("
            SELECT f.id, f.title, template, fc.preset AS preset
            FROM form f
            JOIN form_child fc ON f.id = fc.child_id AND fc.parent_id = ?
            ORDER BY f.title
        ", array($form->id)));


        $smarty->assign('formtypes', $aquarius->get_formtypes()->get_formtypes());

        $smarty->assign('permission_levels', db_Users::$status_names);

        $page_requisites = new Page_Requisites();
        $page_requisites->add_managed_js('jquery.js',          true);
        $page_requisites->add_managed_js('jquery.labelify.js', true);
        $smarty->assign('page_requisites', $page_requisites);

        $result->use_template("formedit.tpl");
    }
}


class action_formedit_save extends action_formedit implements ChangeAction {
    function process($aquarius, $post, $result) {
        $result->touch_region('content');
        $form = $this->load_form();

        // Save form properties
        $form->title = get($post, "formtitle");
        $form->template = get($post, "formtemplate");
        $form->sort_by = get($post, "formsortby");
        $form->sort_reverse = get($post, "formsortreverse", 0);
        $form->fall_through = get($post, "formfallthrough");
        $form->show_in_menu = get($post, "formshowinmenu", 0);
        $form->fieldgroup_selection_id = get($post, 'fieldgroup_selection');
        $form->permission_level = get($post, 'permission_level');
        $form->content_type = trim(get($post, 'content_type'));
        if ($form->id == 'new') {
            $form->insert();
        } else {
            $form->update();
        }

        // Save fields
        $fielddata = get($post, "field");
        $maxweight = 0;
        if (is_array($fielddata)) {
            foreach($fielddata as $id => $data) {
                $original = false;
                $field = DB_DataObject::factory('form_field');

                // See whether we have to insert it
                $new = (bool)preg_match('/^new.*/', $id);

                if ($new) {
                    $field->form_id = $form->id;
                } else {
                    if (!$field->get($id)) throw new Exception("Missing form field with id '$id'; it doesn't exist");
                    $original = clone $field;
                }

                // Read field properties
                $props = array('name', 'description', 'weight', 'type', 'sup1', 'sup2', 'sup3', 'sup4');
                foreach($props as $prop) {
                    $val = get($data, $prop);
                    if ($val === false) throw new Exception("Expected val '$prop' for field $id not in REQUEST");
                    $field->$prop = $val;
                }

                // Special handling for checkboxes
                $field->multi                = get($data, 'multi', 0);
                $field->language_independent = get($data, 'language_independent', 0);
                $field->add_to_title         = get($data, 'add_to_title', 0);
                $active                      = get($data, 'active');

                // Field names must conform to PHP variable naming standards
                if ($active && !preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $field->name)) {
                    // Remove invalid characters from field name, we presume they were accidentally entered and undesired.
                    $clean_name = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]+/', '', $field->name);

                    // First character of field name may not be a number
                    if (preg_match('/^[0-9]/', $clean_name)) {
                        $clean_name = 'n'.$clean_name;
                    }
                    $result->add_message(new FixedTranslation("Invalid field name '$field->name' changed to '$clean_name'"));
                    $field->name = $clean_name;
                }

                // save new permission_level
                $field->permission_level = get($data, 'permission_level', 2);

                // Adjust weight if it's not set
                if (!is_numeric($field->weight)) $field->weight = $maxweight + 10;
                $maxweight = max($maxweight, $field->weight);

                if ($new) {
                    // Insert new fields only if they were given a name
                    if ($active && strlen($field->name) > 0) {
                        $field->id = null;
                        $saved = $field->insert();
                        if (!$saved) throw new Exception("Unable to save new field $field->name");
                        $result->add_message(new Translation('s_message_form_inserted_field',  $field->name));
                    }
                } else {
                    if ($active) {
                        // Remove the inherited flag when the field has been modified
                        if ($original->diff($field)) $field->inherited = false;
                        
                        $field->update();
                    } else {
                        $field->delete();
                        $result->add_message(new Translation('s_message_form_deleted_field',  $field->name));
                    }
                }
            }
        }
        
        $this->reform($form, false, $result);
    }
}


class action_formedit_children_edit extends action_formedit implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $form = $this->load_form();
        $smarty->assign('title', 'Edit form children for '.$form);
        $smarty->assign('radios', true);
        $smarty->assign('checkboxes', true);
        $smarty->assign('saveaction', Action::make('formedit', 'children_save', $form->id));

        $smarty->assign('forms', $aquarius->db->queryhash("
            SELECT f.id, f.title, template, fc.preset AS selected, fc.parent_id AS checked
            FROM form f
            LEFT JOIN form_child fc ON f.id = fc.child_id AND fc.parent_id = ?
            ORDER BY f.title
        ", array($form->id)));

        $result->use_template("form_select.tpl");
    }
}


class action_formedit_children_save extends action_formedit implements ChangeAction {
    function process($aquarius, $post, $result) {
        $result->touch_region('content');
        $form = $this->load_form();

        $aquarius->db->query("START TRANSACTION");
        $aquarius->db->query("DELETE FROM form_child WHERE parent_id = ?", array($form->id));
        foreach(get($post, 'checked_forms', array()) as $form_child) {
            $aquarius->db->query("INSERT INTO form_child SET parent_id = ?, child_id = ?, preset = ?", array($form->id, intval($form_child), get($post, 'selected_form') == $form_child));
        }
        $aquarius->db->query("COMMIT");
    }
}


class action_formedit_inherit_edit extends action_formedit implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $form = $this->load_form();
        $smarty->assign('title', 'Edit form children for '.$form);
        $smarty->assign('checkboxes', true);
        $smarty->assign('reset_option', true);
        $smarty->assign('saveaction', Action::make('formedit', 'inherit_save', $form->id));

        $smarty->assign('forms', $aquarius->db->queryhash("
            SELECT f.id, f.title, template, fc.child_id AS checked
            FROM form f
            LEFT JOIN form_inherit fc ON f.id = fc.parent_id AND fc.child_id = ?
            WHERE f.id <> ?
            ORDER BY f.title
        ", array($form->id, $form->id)));

        
        $result->use_template("form_select.tpl");
    }
}


class action_formedit_inherit_save extends action_formedit implements ChangeAction {
    function process($aquarius, $post, $result) {
        $result->touch_region('content');
        $form = $this->load_form();

        $aquarius->db->query("START TRANSACTION");
        $aquarius->db->query("DELETE FROM form_inherit WHERE child_id = ?", array($form->id));
        foreach(get($post, 'checked_forms', array()) as $form_child) {
            $aquarius->db->query("INSERT INTO form_inherit SET child_id = ?, parent_id = ?", array($form->id, intval($form_child)));
        }
        $aquarius->db->query("COMMIT");
        
        $this->reform($form, get($post, 'reset'), $result);
    }
}
