<?php

/** Create an installer pack of the current installation */
class action_pack extends AdminAction {

    /** These actions are superadmin only */
    function permit_user($user) {
        return $user->isSuperadmin();
    }

    function get_title() {
        return new FixedTranslation('Installer pack');
    }

}

/** Display possible actions */
class action_pack_dialog extends action_pack implements DisplayAction {

    function process($aquarius, $request, $smarty, $result) {
        $smarty->assign('actions', array(
            Action::make('db_maintenance', 'dump'),
            Action::make('pack', 'run')
        ));
        $result->use_template('confirm_actions.tpl');
    }
}

/** Rebuild the node cache of all nodes, including tree index */
class action_pack_run extends action_pack implements ChangeAction {

    function get_title() {
        return new FixedTranslation('Create pack');
    }

    function process($aquarius, $post, $result) {
        $packer = new Aquarius_Packer();
        $file = $packer->pack($aquarius, array('all'), array('inline' => true));
        $result->add_message(new FixedTranslation("Created installer pack $file"));
    }
}
