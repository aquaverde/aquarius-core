<?php 
require_once "lib/spinner.class.php";

class action_feuser extends AdminAction {

    var $props = array("class", "command", "id", "spinner");

    /** Siteadmins permitted */
    function permit_user($user) {
        return $user->isSiteAdmin();
    }
}

class action_feuser_list extends action_feuser implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $fe_users = DB_DataObject::factory('fe_users');
        $fe_groups = DB_DataObject::factory('fe_groups');


        // set the spinner to default if not set
        if ( empty($this->spinner) ) $this->spinner = 0;

        // Read filter restrictions
        $user_search = "";
        $group_search = 0;
        if (in_array('filter_reset', $this->params)) {
            $this->params = array();
        } else {
            // Filter restrictions may be passed as action parameters or as request parameters
            $user_search = get($this->params, 0);
            $group_search = intval(get($this->params, 1));
            $user_search = get($request, 'user_search', $user_search);
            $group_search = intval(get($request, 'group_search', $group_search));

            // Sync action parameters with read values (they are needed in the spinner)
            $this->params[0] = $user_search;
            $this->params[1] = $group_search;
        }

        if ( !empty($user_search) ) {
            // Name's Bob, Bob';DROP *;--
            $fe_users->whereAdd('name LIKE '.$aquarius->db->quote("%$user_search%"));
        }

        // Maybe restrict search to one group
        if ( $group_search > 0 ) {
            $fe_groups2user = DB_DataObject::factory('fe_groups2user');
            $fe_groups2user->group_id = $group_search;
            $fe_users->joinAdd($fe_groups2user);
        }

        // now fetch the data
        $fe_users->orderBy('name');
        $fe_users->find();
        $users = array();

        while ( $fe_users->fetch() ) $users[] = clone($fe_users);

        // initialize the spinner if we need one
        $hasSpinner = count($users) > MAX_FE_USERS_PER_PAGE;
        $smarty->assign("hasSpinner", $hasSpinner);
        if ($hasSpinner) {
            $set_index_function = create_function('$action, $index', '$action->spinner = $index; return $action;');
            $spinner = new Spinner($this->spinner, MAX_FE_USERS_PER_PAGE, count($users), $this, $set_index_function);
            $smarty->assign("spinner", $spinner);
            $users = $spinner->current_slice($users);
        }
                
        $action_generators = $aquarius->execute_hooks('feuser_list_actions');

        // assign the vars
        $smarty->assign("users", $users);
        $smarty->assign("user_search", $user_search);
        $smarty->assign("group_search", $group_search);
        $smarty->assign("groups", $fe_groups->getAllGroups(true));
        $smarty->assign("user_action_generators", $action_generators);
        $result->use_template('fe_user_list.tpl');
    }
}


class action_feuser_edit extends action_feuser implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $fe_users = DB_DataObject::factory('fe_users');
        $fe_groups = DB_DataObject::factory('fe_groups');

        $fe_users->get($this->id);
        $fe_users->loadGroupRelations();

        $smarty->assign("groups", $fe_groups->getAllGroups(true));
        $smarty->assign("user", $fe_users);
        $result->use_template('fe_user_edit.tpl');
    }
}


class action_feuser_save extends action_feuser implements ChangeAction {
    function process($aquarius, $post, $result) {
        $fe_users = DB_DataObject::factory('fe_users');
        $fe_groups = DB_DataObject::factory('fe_groups');
        
        $fe_users->name = get($post, 'name');

        // check if password changed
        $new_password = get($post, 'new_password');
        if (!empty($new_password)) {
            $fe_users->password = md5($new_password);
            $result->add_message(new Translation('s_new_password'));
        }

        $fe_users->active = requestvar('isActive');

        if ( $this->id != "null" ) {
            $fe_users->id = $this->id;
            $fe_users->update();
        } else {
            $fe_users->insert();
        }
        $result->add_message(new Translation('s_user_updated'));

        // store group relations
        $fe_groups2user = DB_DataObject::factory('fe_groups2user');
        $fe_groups2user->storeGroups2User($fe_users->id, requestvar('groups2user'));
    }
}


class action_feuser_toggle_active extends action_feuser implements ChangeAction {
    function process($aquarius, $post, $result) {
        $fe_user = DB_DataObject::factory('fe_users');

        $fe_user->get($this->id);
        $fe_user->active = !$fe_user->active;
        $fe_user->update();
        $result->add_message("User switched");
    }
}


class action_feuser_delete extends action_feuser implements ChangeAction {
    function process($aquarius, $post, $result) {
        $fe_user = DB_DataObject::factory('fe_users');
        
        $fe_user->get($this->id);
        $fe_user->delete();
        $result->add_message("User deleted");
    }
}


class action_feuser_export extends action_feuser implements SideAction {
    function process($aquarius, $request) {
        $fe_user = DB_DataObject::factory('fe_users');
        
        // Read filter restrictions
        $user_search = "";
        $group_search = 0;
        if (!in_array('filter_reset', $this->params)) {
            $user_search = requestvar('user_search');
            $group_search = intval(requestvar('group_search'));
        }
        
        // Get the adresses
        $fe_address = DB_DataObject::factory('fe_address');

        $group_join = "";
        $group_pred = "";
        if ($group_search > 0) {
            $group_join = "JOIN fe_groups2user ON fe_groups2user.user_id = fe_users.id";
            $group_pred = "AND fe_groups2user.group_id = $group_search";

        }

        $name_pred = "";
        if (!empty($user_search)) {
            $name_pred = 'AND fe_users.name LIKE '.$aquarius->db->quote("%$user_search%");
        }

       
        $query = "SELECT fe_users.name, `fe_address`.*
            FROM fe_users
            $group_join
            LEFT JOIN fe_user_address ON fe_user_address.fe_user_id = fe_users.id
            LEFT JOIN fe_address ON fe_user_address.fe_address_id = fe_address.id
            WHERE 1=1
            $group_pred
            $name_pred
        ";

        $fe_address->query($query);
        $adresses = $fe_address;
        $adressesarray = array();
        while($adresses->fetch()) {
            $adressesarray[] = $adresses->toArray();
        }

        array_walk_recursive($adressesarray, create_function(
            '&$item,$key', '$item = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $item);'));

        ob_clean();
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="adresses.csv"');

        $fp = fopen("php://output", "w");
        if (!$fp) throw new Exception("Unable to open output to write CSV");

        $columns = array_keys($adresses->table());
        aqua_fputcsv($fp, $columns);
        foreach($adressesarray as $line) {
            aqua_fputcsv($fp, $line);
        }

        fclose($fp);
    }
}

