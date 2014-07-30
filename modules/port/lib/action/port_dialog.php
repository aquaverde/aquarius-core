<?php
/** Copy content fields to a new name */
class action_port_dialog extends ModuleAction implements DisplayAction {
    var $modname = "port";
    var $props = array('class');

    function get_title() {
        return new Translation('port_dialog_title');
    }

    function valid($user) {
      return $user->isSuperadmin();
    }

    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('roots_select_action', Action::make('nodes_select', 'tree', 'export', $smarty->get_template_vars('lg'), 'root', false, '', true));
        $smarty->assign('export_actions', array(
            Action::make('port', 'export_show'),
            Action::make('port', 'export_download'),
            Action::make('cancel')
        ));
        $smarty->assign('import_root_select_action', Action::make('nodes_select', 'tree', 'import', $smarty->get_template_vars('lg'), 'root', false, '', false));
        $smarty->assign('import_actions', array(
            Action::make('port', 'import'),
            Action::make('cancel')
        ));
        $result->use_template('port_dialog.tpl');
    }
}
