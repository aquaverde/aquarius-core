<?

class action_cache_cleaner extends AdminAction {

    var $props = array('class', 'command');

    /** allows superadmins */
    function permit_user($user) {
        return $user->isSuperadmin();
    }
}

/** The smarty cache cleaner takes two parameters:
  * 1. selected_end: Were to clean; 'backend', 'frontend' or 'all'
  * 2. part: either 'cache' or 'compile'. 'compile' clears cache as well. */
class action_cache_cleaner_smarty extends action_cache_cleaner implements ChangeAction {
    function get_title() { return new FixedTranslation("Clear {$this->selected_end} {$this->part}"); }
    var $props = array('class', 'command', 'selected_end', 'part');
    function process($aquarius, $post, $result) {
        foreach(array('frontend', 'backend') as $end) {
            if ($this->selected_end == 'all' || $this->selected_end == $end) {
                $smarty = $aquarius->{"get_smarty_{$end}_container"}(false);
                switch($this->part) {
                    case 'compile':
                        $smarty->clear_compiled_tpl();
                        $result->add_message(new FixedTranslation("$end compiled templates cleared"));
                    case 'cache':
                        $smarty->clear_all_cache();
                        $result->add_message(new FixedTranslation("$end cache cleared"));
                        break;
                    default: throw new Exception("What that '$this->part', you talkin of?");
                }
            }
        }
    }
}

class action_cache_cleaner_smarty_select extends action_cache_cleaner implements DisplayAction {
    function get_title() { return new FixedTranslation("Smarty cache cleaner"); }
    function process($aquarius, $request, $smarty, $result) {
        foreach(array('frontend', 'backend') as $end) {
            require_once "lib/action_decorators.php";
            $smarty->assign('title', 'Clear smarty caches');
            $smarty->assign('actions', array(
                Action::make('cache_cleaner', 'smarty', 'frontend', 'cache'),
                Action::make('cache_cleaner', 'smarty', 'frontend', 'compile'),
                Action::make('cache_cleaner', 'smarty', 'backend', 'cache'),
                Action::make('cache_cleaner', 'smarty', 'backend', 'compile'),
                new ActionTitleChange(Action::make('cache_cleaner', 'smarty', 'all', 'compile'), 'Mr. Proper')
            ));
            $result->use_template('select.tpl');
        }
    }
}

?>