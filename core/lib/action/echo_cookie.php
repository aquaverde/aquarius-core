<?

/**
Add a cookie that enables debugging
*/

class action_echo_cookie extends AdminAction {

    var $props = array('class', 'command');

    /** allows superadmins */
    function permit_user($user) {
        return $user->isSuperadmin();
    }

    function get_title() {
        return new FixedTranslation("Set Log echo cookie");
    }
}

class action_echo_cookie_edit extends action_echo_cookie implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $logoptions = array();
        foreach(array("ALL", "SQL", "DEBUG", "INFO", "WARN", "FAIL", "NEVER") as $level) {
            $logoptions[constant("Log::$level")] = $level;
        }
        $smarty->assign('logoptions', $logoptions);
        $smarty->assign('current_logger', $aquarius->logger);
        $smarty->assign('actions', array(
            Action::make('echo_cookie', 'set'),
            Action::make('echo_cookie', 'unset')
        ));
        $result->use_template('echo_cookie.tpl');
    }
}

class action_echo_cookie_unset extends action_echo_cookie implements ChangeAction {
    function get_title() {
        return new FixedTranslation("Unset Cookie");
    }

    function process($aquarius, $post, $result) {
        $aquarius->logging_manager->override_with_cookie(array());
    }
}

class action_echo_cookie_set extends action_echo_cookie implements ChangeAction {
    function get_title() {
        return new FixedTranslation("Set Cookie");
    }
    function process($aquarius, $post, $result) {
        $loglevel = requestvar('loglevel');
        $firelevel = requestvar('firelevel');
        $aquarius->logging_manager->override_with_cookie(array('echo' => $loglevel, 'fire' => $firelevel));
        $result->add_message(new FixedTranslation("Set log echo cookie to $loglevel, $firelevel"));
        
        // HACK: Directly set logging levels so they are active from now
        $aquarius->logger->echolevel = $loglevel;
        $aquarius->logger->firelevel = $firelevel;
    }
}

?>