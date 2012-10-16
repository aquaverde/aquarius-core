<?php
require_once "lib/Compare.php";

class action_formfield extends AdminAction { 

    var $props = array("class", "op");

    /** permit for superuser */
    function permit_user($user) {
        return $user->isSuperadmin();
    }
    
    
    function build_fieldlist() {
        $formfield = DB_DataObject::factory('Form_field');
        $formfield->find();
        
        $interesting_fields = array('type', 'sup1', 'sup2', 'sup3', 'sup4', 'multi', 'language_independent', 'add_to_title', 'permission_level');

        // Group similar formfields
        $groups = array();
        while($formfield->fetch()) {
            $grouping_identifier = $formfield->name."\0".$formfield->description;
            $hashid = sha1($grouping_identifier);
            if (!isset($groups[$hashid])) {
                $groups[$hashid] = array(
                    'name'        => $formfield->name,
                    'description' => $formfield->description,
                    'hashid'      => $hashid,
                    'grouping_identifier' => $grouping_identifier,
                    'formfields'  => array()
                );
            }
            $groups[$hashid]['formfields'] []= clone $formfield;
        }
        uasort($groups, ArrayCompare::by_entry('grouping_identifier'));
        return $groups;
    }
}

class action_formfield_list extends action_formfield implements DisplayAction {
    function get_title() {
        return new FixedTranslation("Formfield list");
    }


    function process($aquarius, $request, $smarty, $result) {
        $interesting_fields = array('type', 'sup1', 'sup2', 'sup3', 'sup4', 'multi', 'language_independent', 'add_to_title', 'permission_level');
        
        $groups = $this->build_fieldlist();
        foreach($groups as &$group) {
            $differences = array();
            foreach($interesting_fields as $ffield) {
                $field_differences = array();
                foreach($group['formfields'] as $formfield) {
                    $value = $formfield->$ffield;
                    if (!isset($field_differences[$value])) $field_differences[$value] = array();
                    $field_differences[$value] []= $formfield->get_form()->title;
                }
                ksort($field_differences);
                $differences[$ffield] = $field_differences;
            }
            $group['delete'] = Action::make('formfield', 'delete_confirm', $group['hashid']);
            $group['differences'] = $differences;
        }
        $smarty->assign('interesting_fields', $interesting_fields);
        $smarty->assign('groups', $groups);
        $change_action = Action::make('formfield', 'change_descriptions');
        $smarty->assign('change_action', $change_action);
        
        $smarty->assign('actions', array(
            $change_action
        ));
        
        
        $page_requisites = new Page_Requisites();
        $page_requisites->add_managed_js('jquery.js',          true);
        $page_requisites->add_managed_js('jquery.labelify.js', true);
        $smarty->assign('page_requisites', $page_requisites);

        $result->use_template("formfield_list.tpl");
    }
}

class action_formfield_change_descriptions extends action_formfield implements ChangeAction {
    function get_title() {
        return new FixedTranslation("Save changed descriptions");
    }

    function process($aquarius, $post, $result) {
        $fieldlist = $this->build_fieldlist();
        foreach($fieldlist as $field) {
            $hashid = $field['hashid'];
            if (isset($post['descr'][$hashid])) {
                $new_descr = $post['descr'][$hashid];
                if ($new_descr != $field['description']) {
                    $forms_changed = array();
                    foreach($field['formfields'] as $formfield) {
                        $forms_changed []= $formfield->get_form()->title;
                        $formfield->description = $new_descr;
                        $formfield->update();
                    }
                    $result->add_message(new FixedTranslation("Saving description '".$new_descr."' for '".$field['name']."' in forms: ".join(', ', $forms_changed)));
                }
            }
        }
    }
}

class action_formfield_delete_confirm extends action_formfield implements DisplayAction {
    var $props = array("class", "op", 'hashid');

    function get_icon()  { return 'buttons/delete.gif'; }
    
    function get_title() {
        return new FixedTranslation("Delete all fields");
    }

    function process($aquarius, $params, $smarty, $result) {
        $groups = $this->build_fieldlist();
        $group = get($groups, $this->hashid);
        if (!$group) throw new Exception("Can't find field group ".$this->hashid);
        
        $list = array();
        foreach($group['formfields'] as $formfield) {
            $form = $formfield->get_form();
            $list []= $form->title;
        }
        $smarty->assign('text_top', "Delete field '".$group['name']."' from all these forms?");
        $smarty->assign('list', $list);
        $smarty->assign('actions', array(
            Action::make('formfield', 'delete', $this->hashid),
            Action::make('cancel')
        ));
        $result->skip_return();
        $result->use_template("confirm_list.tpl");
    }
}

class action_formfield_delete extends action_formfield implements ChangeAction {
    var $props = array("class", "op", 'hashid');

    function get_title() {
        return new FixedTranslation("Delete from all forms");
    }

    function process($aquarius, $post, $result) {
        $groups = $this->build_fieldlist();
        $group = get($groups, $this->hashid);
        if (!$group) throw new Exception("Can't find field group ".$this->hashid);

        foreach($group['formfields'] as $formfield) {
            $formfield->delete();
        }

        $result->add_message(new FixedTranslation("Deleted '".$group['name']."' fields"));
    }
}
?>