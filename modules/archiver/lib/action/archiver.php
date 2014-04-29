<?php 

class action_archiver extends ModuleAction {
	var $modname = "archiver";
    var $props = array('class', 'op');

    function valid($user) {
      return (bool)$user;
    }
        
    function get_title() {
        return new Translation('archiver_archive');
    }
}

class action_archiver_runconfirm extends action_archiver implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('text', new Translation('archiver_question'));
        $smarty->assign('actions', array(Action::make('archiver', 'run')));
        $result->use_template('confirm_actions.tpl');
    }
}

class action_archiver_run extends action_archiver implements ChangeAction {
    function process($aquarius, $post, $result) {
        $aquarius->modules[$this->modname]->run();
        $result->add_message(new FixedTranslation('Archived Contents'));
    }
}

