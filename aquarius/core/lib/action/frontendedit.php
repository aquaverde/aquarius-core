<?php

class action_frontendedit extends AdminAction {

    var $props = array('class', 'command');

    function permit_user($user) {
        return (bool)$user;
    }

}

/** Simple action to close the window after editing in the frontend
  */
class action_frontendedit_close extends action_frontendedit implements DisplayAction {

    var $props = array('class', 'command');

    function permit_user($user) {
        return (bool)$user;
    }

    function process($aquarius, $get, $smarty, $result) {
        $result->use_template('frontendedit_close.tpl');
    }
}
