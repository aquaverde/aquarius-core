<?
class action_feuser extends AdminAction {

    var $props = array("class", "command", "id", "spinner");

    /** Siteadmins permitted */
    function permit_user($user) {
        return $user->isSiteAdmin();
    }

    function execute() {
        global $DB;
        global $aquarius;

        $smarty = false;
        $messages = array();

        require_once "lib/spinner.class.php";

        $fe_users = DB_DataObject::factory('fe_users');
        $fe_groups = DB_DataObject::factory('fe_groups');

        switch ($this->command) {
            case 'list':
                $smarty = $aquarius->get_smarty_backend_container();

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
                    $user_search = requestvar('user_search', $user_search);
                    $group_search = intval(requestvar('group_search', $group_search));

                    // Sync action parameters with read values (they are needed in the spinner)
                    $this->params[0] = $user_search;
                    $this->params[1] = $group_search;
                }

                if ( !empty($user_search) ) {
                    $fe_users->whereAdd("name LIKE '%".mysql_real_escape_string($user_search)."%'");
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
                $smarty->tmplname = 'fe_user_list.tpl';
                break;

            case 'edit':
                $smarty = $aquarius->get_smarty_backend_container();
                $fe_users->get($this->id);
                $fe_users->loadGroupRelations();

                $smarty->assign("groups", $fe_groups->getAllGroups(true));
                $smarty->assign("user", $fe_users);
                $smarty->tmplname = 'fe_user_edit.tpl';
                break;

            case 'save':
                $fe_users->name = requestvar('name');

                // check if password changed
                $new_password = requestvar('new_password');
                if (!empty($new_password)) {
                    $fe_users->password = md5($new_password);
                    $messages[] = "s_new_password";
                }

                $fe_users->active = requestvar('isActive');

                if ( $this->id != "null" ) {
                    $fe_users->id = $this->id;
                    $fe_users->update();
                } else {
                    $fe_users->insert();
                }
                $messages[] = "s_user_updated";

                // store group relations
                $fe_groups2user = DB_DataObject::factory('fe_groups2user');
                $fe_groups2user->storeGroups2User($fe_users->id, requestvar('groups2user'));
                break;

            case "toggle_active":
                $fe_users->get($this->id);
                $fe_users->active = !$fe_users->active;
                $fe_users->update();
                $messages[] = "User switched";
                break;

            case "delete":
                $fe_users->get($this->id);
                $fe_users->delete();
                $messages[] = "User deleted";
                break;
	    
	    case "export":
		
		$file = FILEBASEDIR.'/download/fe_adresses.csv';
		
		// Read filter restrictions
		$user_search = "";
                $group_search = 0;
                if (!in_array('filter_reset', $this->params)) {
                    $user_search = requestvar('user_search');
                    $group_search = intval(requestvar('group_search'));
                }
		
		// Get the adresses
		$fe_address = DB_DataObject::factory('fe_address');
		
		$query = "SELECT `fe_address`.* FROM {$fe_address->__table}, fe_users, fe_user_address ";
		
		// add the fe_groups2user in the request, in the case we need it
		if($group_search > 0) {
		    $query .= ", fe_groups2user ";
		}
		
		$query .= "WHERE fe_user_address.fe_user_id = fe_users.id and fe_user_address.fe_address_id = fe_address.id ";

                if ( !empty($user_search) ) {
		    $query .= "and fe_users.name LIKE '%" . mysql_real_escape_string($user_search) . "%' ";
		}

                // Maybe restrict search to one group
                if ( $group_search > 0 ) {
		    $query .= "and fe_groups2user.user_id = fe_users.id and fe_groups2user.group_id = $group_search";
                }
		
		$fe_address->query($query);
		$adresses = $fe_address;
	        $adressesarray = array();
	        while($adresses->fetch()) {
		    $adressesarray[] = $adresses->toArray();
		}
		
		print_r($adressesarray);
		
		array_walk_recursive($adressesarray, create_function(
		    '&$item,$key', '$item = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $item);'));
		$fp = fopen($file, 'w');
          
		$columns = array_keys($adresses->table());
		if(!function_exists("fputcsv")) {
		    aqua_fputcsv($fp, $columns,';','"');
		} else {
		    fputcsv($fp, $columns,';','"');
		}
		foreach($adressesarray as $line) {
		    if(!function_exists("fputcsv")) {
			aqua_fputcsv($fp, $line,';','"');
		    } else {
			fputcsv($fp, $line,';','"');
		    }
		}
            
		fclose($fp);
		
		
		ob_clean();
		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="' . $file . '"');
		readfile($file);
		exit();
		break;
	    
            default:
                throw new Exception("Operation unknown: '$this->command'");
        }
        
        return array('messages'=>$messages, 'smarty'=>$smarty);
    }

}
?>