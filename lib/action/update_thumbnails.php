<?
/** (Re)generate thumbnails for managed files
    @package Aquarius.backend
*/

class action_update_thumbnails extends AdminAction {
    var $props = array('class', 'command');
    /** allowed for all users */
    function permit_user($user) {
        return $user->isSiteadmin();
    }

    function for_selected_dirs($post, $operation) {
        require_once "lib/file_mgmt.lib.php";
        $all_dirs = get_cached_dirs('');
        $dir = $post['dir'];
        if ($dir == 'all') {
            $dirs = $all_dirs;
        } else {
            $dirs = array($dir);
        }
        foreach($dirs as $dir) {
            if (!in_array($dir, $all_dirs)) throw new Exception("Invalid dir $dir");
            call_user_func(array($this, $operation), $dir);
        }
    }
}

class action_update_thumbnails_update extends action_update_thumbnails implements ChangeAction {
    var $props = array('class', 'command', 'force');
    function get_title() {
        return new Translation($this->force?'s_regenerate':'s_update');
    }
    function process($aquarius, $post, $result) {
        $this->for_selected_dirs($post, 'update_thumbs');
        $result->add_message(new Translation('s_message_update_thumbnails'));
    }

    function update_thumbs($dir) {
        update_directory_thumbnails($dir, (bool)$this->force);
    }
}

class action_update_thumbnails_fix_permissions extends action_update_thumbnails implements ChangeAction {
    function permit_user($user) {
        return $user->isSuperadmin();
    }
    function get_title() {
        return new Translation('filemanager_fix_permissions');
    }
    function process($aquarius, $post, $result) {
        $this->for_selected_dirs($post, 'fix_permissions');
        $result->add_message(new Translation('s_message_permissions_fixed'));
    }

    function fix_permissions($dir) {
        $dir_iter = new DirectoryIterator(ensure_filebasedir_path($dir));
        foreach($dir_iter as $dir_entry) {
            if ($dir_entry->isFile() && !$dir_entry->isDot()) { // We do not mess with dot-files, they are most likely not something we should change permissions of.
                fix_file_permissions($dir_entry->getPathName());
            }
        }
    }
}

class action_update_thumbnails_prompt extends action_update_thumbnails implements DisplayAction {
    function get_title() {
        return new Translation('menu_filemgr_update_thumbnails');
    }
    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/file_mgmt.lib.php";
        $dirs = get_cached_dirs('');
        array_unshift($dirs, 'all');
        $dir_names = $dirs;
        $dir_names[0] = str(new Translation('s_all'));
        $smarty->assign('dirs', $dirs);
        $smarty->assign('dir_names', $dir_names);
        $smarty->assign('actions', array_filter(array(
            Action::make('update_thumbnails', 'update', 0),
            Action::make('update_thumbnails', 'update', 1),
            Action::make('update_thumbnails', 'fix_permissions'),
            Action::make('cancel')
        )));
        $result->use_template('update_thumbnails.tpl');
    }
}
?>

