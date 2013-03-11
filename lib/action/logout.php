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
      $result->inject_action(Action::make('logout', 'redirect'));
    }
}

/** Redirect to the login screen */
class action_logout_redirect extends action_logout implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
      $result->use_template("login-redirect.tpl");
    }
}
?>