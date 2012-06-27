<?php
class action_formlist extends AdminAction implements DisplayAction { 
    
    var $props = array("class", "op");
    
    /** permit for superuser */
    function permit_user($user) {
        return $user->isSuperadmin();
    }
    
    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/db/Form.php";

        $smarty->assign('forms', db_Form::get_all());
        $smarty->assign('action_new', Action::make('formedit', 'edit', 'new'));
        $result->use_template("formlist.tpl");
    }
}
?>