<?

require_once "lib/Maintenance_Mode_Control.php";

class action_maintenance_mode extends AdminAction {

    var $props = array('class', 'command');

    /** allows superadmins */
    function permit_user($user) {
        return $user->isSuperadmin();
    }
}

/** Activate maintenance mode */
class action_maintenance_mode_start extends action_maintenance_mode implements ChangeAction {
    function get_title() { return new FixedTranslation("Start maintenance mode for me"); }
    function process($aquarius, $post, $result) {
        $set = Maintenance_Mode_Control::enable(2);
        $result->add_message(new FixedTranslation("Maintenance mode activated until {$set['datestr']}, for host {$set['host']}"));
    }
}

/** Disable maintenance */
class action_maintenance_mode_stop extends action_maintenance_mode implements ChangeAction {
    function get_title() { return new FixedTranslation("Stop maintenance mode"); }
    function process($aquarius, $post, $result) {
        $disabled = Maintenance_Mode_Control::disable();
        if ($disabled) {
            $result->add_message(new FixedTranslation("Maintenance mode disabled"));
        }
    }
}

class action_maintenance_mode_dialog extends action_maintenance_mode implements DisplayAction {
    function get_title() { return new FixedTranslation("Maintenance mode"); }
    function process($aquarius, $request, $smarty, $result) {
        list($code, $reason) = Maintenance_Mode_Control::validate();
        $result->add_message(new FixedTranslation($reason));
        if ($code === 0) {
            $result->add_message(new FixedTranslation("Maintenance mode active for somebody else!"));
        }

        if ($code === 1) {
            $result->add_message(new FixedTranslation("Maintenance mode active for you."));
        }

        $smarty->assign('title', 'Maintenance mode control');
        $smarty->assign('actions', array(
            Action::make('maintenance_mode', 'start'),
            Action::make('maintenance_mode', 'stop')
        ));
        $result->use_template('select.tpl');
    }
}
