<?php 
class action_pointing_legend_ajax extends AdminAction {

    var $props = array("class", "request");

    /** Always permits for logged-in users */
    function permit_user($user) {
      return (bool)$user;
    }
}

class action_pointing_legend_ajax_empty_row extends action_pointing_legend_ajax implements DisplayAction {


    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";

        $result->skip_return();

        // LOAD FORMFIELD
        $form_field = db_Form_field::staticGet(get($request, 'formfield'));
        if (!$form_field) throw new Exception("Invalid form_field id '$form_field_id' in request");

        // FAKE CONTENT
        $content = new StdClass();
        $content->lg = get($request, 'lg');

        $new_id = intval(get($request, 'new_id'));

        Action::use_class('contentedit');

        $value = array($new_id => array());
        $field = action_contentedit_edit::prepare_container(false, $content, $form_field, $form_field->name, $value, array());

        $fileval = first($field['value']);
        $fileval['myindex'] = $new_id;
        $fileval['popupid'] = $field['htmlid']."_".$new_id;
        $fileval['weight'] = $new_id * 10 + 10;
        $fileval['ajax'] = true;
        
        $smarty->assign('field', $field);
        $smarty->assign('fileval', $fileval);
        $smarty->assign('content', $content);
        $result->use_template('formfield.pointing_legend.row.tpl');
    }
}
?>