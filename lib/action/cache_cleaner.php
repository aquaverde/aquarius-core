<?php

class action_cache_cleaner extends AdminAction {

    var $props = array('class', 'command');

    /** allows superadmins */
    function permit_user($user) {
        return $user->isSiteadmin();
    }
    
    static function all_cleansing_actions() {
        return array_filter(array(
            Action::make('cache_cleaner', 'smarty_frontend'),
            Action::make('cache_cleaner', 'smarty_backend'),
            Action::make('cache_cleaner', 'content'),
            Action::make('cache_cleaner', 'loader')
        ));
    }
}

class action_cache_cleaner_dialog extends action_cache_cleaner implements DisplayAction {
    function get_title() { return new Translation("clear_caches"); }
    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/action_decorators.php";
        $cleansing_actions = array_merge(array(Action::make('cache_cleaner', 'all')), self::all_cleansing_actions());
        $cleansing_actions []= Action::make('cancel');
        $smarty->assign('actions', $cleansing_actions);
        $smarty->assign('message', new Translation('clear_caches_message'));
        $result->use_template('select.tpl');
    }
}

class action_cache_cleaner_all extends action_cache_cleaner implements ChangeAction {
    function get_title() { return new Translation("clear_caches_all"); }
    function process($aquarius, $post, $result) {
        $cleansing_actions = self::all_cleansing_actions();
        foreach( self::all_cleansing_actions() as $cleansing_action) {
            $cleansing_action->process($aquarius, $post, $result);
        }
    }
}

class action_cache_cleaner_smarty_frontend extends action_cache_cleaner implements ChangeAction {
    function get_title() { return new Translation("clear_cache_frontend"); }
    function process($aquarius, $post, $result) {
        $smarty = $aquarius->get_smarty_frontend_container(false);
        $smarty->clear_compiled_tpl();
        $smarty->clear_all_cache();
        $result->add_message(new Translation("clear_cache_frontend_cleared"));
    }
}

class action_cache_cleaner_smarty_backend extends action_cache_cleaner implements ChangeAction {
    function get_title() { return new Translation("clear_cache_backend"); }
    function process($aquarius, $post, $result) {
        $smarty = $aquarius->get_smarty_backend_container();
        $smarty->clear_compiled_tpl();
        $smarty->clear_all_cache();
        $result->add_message(new Translation("clear_cache_backend_cleared"));
    }
}

class action_cache_cleaner_content extends action_cache_cleaner implements ChangeAction {
    function get_title() { return new Translation("clear_cache_content"); }
    function process($aquarius, $post, $result) {
        $result->touch_region('content');
        $result->add_message(new Translation("clear_cache_content_cleared"));
    }
}

class action_cache_cleaner_loader extends action_cache_cleaner implements ChangeAction {
    function get_title() { return new Translation("clear_cache_loader"); }
    function process($aquarius, $post, $result) {
        $result->touch_region('loader');
        $result->add_message(new Translation("clear_cache_loader_cleared"));
    }
}
