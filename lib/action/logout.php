<?php 
class action_logout extends AdminAction {

    var $props = array('class', 'op');

    /** Always permitted */
    function permit() {
      return true;
    }
}

class action_logout_now extends action_logout implements ChangeAction {
    function process($aquarius, $post, $result) {
      db_Users::logout();
    }
}

