<?php 
abstract class action_user extends AdminAction {
    
    var $props = array("class", "command", "id");
    
    /** Only siteadmins and above may change user's settings
      * Yes, this means that mere users currently do not have the ability to change their password. Pathetic, really. */
    function permit_user($user) {
        // Mere users may do nothing
        if (!$user || !$user->isSiteadmin()) return false;
        
        if ($this->command == 'showList') return true;
        
        // Ensure correct user id
        $edituser = false;
        if ($this->id != 'new') {
            $edituser = db_Users::staticGet($this->id);
            if (!$edituser) return false; // Cannot edit inexisting users, can you?
        }
        
        // Users can't delete themselves
        if ($this->command == 'deleteUser' && $this->id == $user->id) return false;

        // Users may only edit new users and users below their status
        if ( $user->isSiteadmin() ) {
            return ($this->id == 'new' || $edituser->status >= $user->status);
        }
        
        return false;
    }
}

class action_user_showList extends action_user implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $editing_user = db_Users::authenticated();
        $myUsers = $editing_user->visible_users();
        $smarty->assign('user_statuses', $editing_user->visible_status_names());
        $smarty->assign('users', $myUsers);
        $result->use_template('userlist.tpl');
    }
}

class action_user_saveUser extends action_user implements ChangeAction {
    function process($aquarius, $post, $result) {
        require_once "lib/db/Languages.php";
        require_once "lib/db/Users2nodes.php";
        require_once "lib/db/Users2languages.php";
        require_once "lib/db/Users2modules.php";

        $editing_user = db_Users::authenticated();
        $user =& DB_DataObject::factory('users');
        
        $fields = array('name','adminLanguage','defaultLanguage');

        foreach ($fields as $field) {
            $user->$field = get($post, $field);
        }

        // check if password changed
        $pw = get($post, 'user_password');
        if (strlen($pw)) {
            $user->set_password($pw);
            $result->add_message(new Translation('s_new_password'));
        }

        // Siteadmins can change more settings
        if ($editing_user->isSiteAdmin()) {
            // Update user's status
            $status = get($post, 'status');
            $user->status = max($editing_user->status, $status); // Cap requested status level at editing user's level to avoid privilege escalation

            // Update activation permission
            $user->activation_permission = get($post, 'activation_permission');
            $user->delete_permission = get($post, 'delete_permission');
            $user->copy_permission = get($post, 'copy_permission');
        }

        // save the user data
        if ( $this->id == "new" ) {
            $user->active = 1; // new users are active by default
            $user->insert();
        } else {
            $user->id = $this->id;
            $user->update();
        }
        $result->add_message('s_user_updated');

        // After user has been created or updated, update attached permissions
        if ($editing_user->isSiteadmin()) {

            // Update language access permissions
            db_Users2languages::deleteUser($user->id);
            $langs = get($post, 'users2languages');
            if (!empty($langs)) {
                foreach ($langs as $lg) {
                    db_Users2languages::addUsersLanguage($user->id, $lg);
                }
            }

            // Update module access permissions
            db_Users2modules::deleteUser($user->id);
            $mods = get($post, 'users2modules');
            if (!empty($mods)) {
                foreach ($mods as $mod) {
                    db_Users2modules::addUsersModule($user->id, $mod);
                }
            }

            // Update node permissions
            $users2node = DB_DataObject::factory('users2nodes');
            $users2node->userId = $user->id;
            $users2node->delete();

            $users2node = DB_DataObject::factory('users2nodes');
            $users2node->userId = $user->id;

            $nodes = get($post, 'nodeId');
            if (!empty($nodes)) {
                foreach ($nodes as $nodeId) {
                    $users2node->nodeId = $nodeId;
                    $users2node->insert();
                }
            }
        }
    }
}


class action_user_editUser extends action_user implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        require_once "lib/db/Node.php";
        require_once "lib/db/Languages.php";
        require_once "lib/db/Users2nodes.php";
        require_once "lib/db/Users2modules.php";
        $editing_user = db_Users::authenticated();
        $user =& DB_DataObject::factory('users');

        // load user information if we edit existing
        $user->id = $this->id;
        $loaded = false;
        if ( $this->id != "new" ) {
            $user->find();
            $loaded = $user->fetch();
            if (!$loaded) throw new Exception("Unable to load user for ID '$userid'");
        } else {
            $user->status = db_Users::USER; // Default
        }

        // Show boxes to edit permissions of mere users
        if ($editing_user->isSiteAdmin() && ($user->status >= db_Users::USER)) {
            $depth = $aquarius->conf('admin/user/edit_permission_tree_depth', 2);
            $smarty->assign('availableNodes', $user->getNodes());
            $smarty->assign('nodelist', NodeTree::build_flat(db_Node::get_root(), false, false, false, false, $depth));
        }

        // load the node tree
        $rootnode = db_Node::get_root();
        $nodelist = NodeTree::build_flat($rootnode, array('boxed'));

        // List of languages
        $languages = array();
        foreach ( db_Languages::getLanguages() as $language )
            $languages[$language->lg] = $language->name;

        $modules = array();
        foreach(db_Modules::getModules(true) as $module)
            $modules[$module->id] = $module->short;

        $smarty->assign("user", $user);
        $smarty->assign("loggedUser", $editing_user);
        $smarty->assign('user_statuses', $editing_user->visible_status_names());
        $smarty->assign("checkActive", ($user->active)? 'checked="checked"' : '');
        $smarty->assign("interfaceLanguages", getInterfaceLanguages());
        $smarty->assign("languages", $languages);
        $smarty->assign("title", "Add/Edit user");
        $smarty->assign("adminLanguages", array("de","fr","en"));
        $smarty->assign("selected_languages", $loaded?$user->getAccessableLanguages():array());
        $smarty->assign("modules", $modules);
        $smarty->assign("selected_modules", $loaded?$user->getAccessableModuleIds():array());

        $result->use_template('useredit.tpl');
    }
}


class action_user_deleteUser extends action_user implements ChangeAction {
    function process($aquarius, $post, $result) {
        $user =& DB_DataObject::factory('users');
        $userid = intval($this->id);
        $loaded = $user->get($userid);
        $userName = $user->name;
        $user->delete();
        $result->add_message('User '.$userName.' deleted'); // FIXME translation
    }
}

class action_user_toggle_active extends action_user implements ChangeAction {
    function process($aquarius, $post, $result) {
        $user =& DB_DataObject::factory('users');
        $userid = intval($this->id);
        $loaded = $user->get($userid);
        $user->active = !$user->active;
        $user->update();
        $result->add_message("User switched"); // FIXME translation
    }
}
?>