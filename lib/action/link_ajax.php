<?php 
class action_link_ajax extends AdminAction {

    var $props = array("class", "request");

    /** Always permits for logged-in users */
    function permit_user($user) {
      return (bool)$user;
    }
    
    function load_from($request) {
        return $this->load(get($request, 'formfield'));
    }

    function load($form_field_id) {
        $form_field = db_Form_field::staticGet($form_field_id);
        if (!$form_field) throw new Exception("Invalid form_field id '$form_field_id' in request");

        return compact('form_field');
    }
}

class action_link_ajax_empty_row extends action_link_ajax implements DisplayAction {


    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";

        $result->skip_return();

        extract($this->load_from($request));

        if (!$form_field->multi) throw new Exception("Trying to get empty row for form field $form_field->name, which does not support multiple values");

        $new_id = intval(get($request, 'new_id'));

        Action::use_class('contentedit');

        // We want one empty file value, using $new_id for the file value
        $value = array($new_id => array());
        $field = action_contentedit_edit::prepare_container(false, false, $form_field, $form_field->name, $value, array());

        $fileval = first($field['value']);
        $fileval['myindex'] = $new_id;
        $fileval['weight'] = $new_id * 10 + 10;
        
        $smarty->assign('field', $field);
        $smarty->assign('fileval', $fileval);
        $result->use_template('formfield.link.row.tpl');
    }
}
