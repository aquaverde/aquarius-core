<?php

/** Provide frontend user management functions
  *
  * Create and update user account and associated address data from the frontend
  * Backend user management is extended to include operations on user's address data
  */
class Fe_user_management extends Module {
    var $register_hooks = array('smarty_config_frontend', 'smarty_config_backend', 'feuser_list_filteractions', 'feuser_list_actions');
    
    var $short = "fe_user_management";
    var $name  = "Frontend User Management";

    /** Provide action to export user addresses */
    function feuser_list_filteractions() {
        return Action::make('fe_address', 'export', false);
    }

    /** Provide Object that supplies address viewing/editing actions */
    function feuser_list_actions() {
        return new Edit_Address_Action_Generator();
    }

    /** Create address object from given dict */
    function read_address($params) {
        return new Usermanagement_Address($params);
    }    



    /** Save address and create user
     * The user's email address is used as id
     * @param $params dictionary with fields 'email', 'password' and 'password_confirm'
     * @return dict with 'user' and 'errors'
     * The created user will be logged in for this session
     */
    function register($params) {
        $errors = array();

        $mail_address = get($params, 'email');
        
        require_once "lib/Usermanagement_Address.php";
        if (!Usermanagement_Address::valid_mail_address($mail_address)) {
            $errors['email'] = 'usermanagement_invalid_mail_address';
        }

        $user = DB_DataObject::factory('fe_users');
        $user->name = $mail_address;
        if ($user->find()) {
            $errors['email'] = 'usermanagement_user_exists';
        }

        $password = get($params, 'password');
        if (empty($password)) {
            $errors['password'] = 'usermanagement_password_missing';
        }
        
        if (empty($errors)) {
            $password_confirm = get($params, 'password_confirm');
            if ($password_confirm !== $password) {
                $errors['password_confirm'] = 'usermanagement_passwords_dont_match';
            }
        }
        
        if (empty($errors)) {
            $user->password = md5($password);
            $user->active = true;
            $user->insert();
            Log::info("Added user $user->name($user->id)");

            // Log the user in
            $user->login();

            // Add user to default groups
            foreach($this->conf('autogroups', array()) as $group_id) {
                if (!$user->in_group($group_id)) {
                    $rel = DB_DataObject::factory('fe_groups2user');
                    $rel->user_id = $user->id;
                    $rel->group_id = $group_id;
                    $rel->insert();
                }
            }
            
            // Add address entry
            $address = db_Dataobject::factory('fe_address');
            $inserted = $address->insert();
            if (!$inserted) throw new Exception("Unable add address for $user->name ");
            $fe_user_address = db_Dataobject::factory('fe_user_address');
            $fe_user_address->fe_user_id = $user->id;
            $fe_user_address->fe_address_id = $address->id;
            $inserted = $fe_user_address->insert();
            if (!$inserted) throw new Exception("Unable link address for $user->name ");
        }

        return compact('user', 'errors');
    }
    
    /** Prepare a basic mail instance
      * @param $node the node  for this mail
      * @param $template name of the template to be used
      * @param $vars template variables for the mail templates
      * */
    function prepare_mail($node, $template, $vars) {
        require_once('lib/aquamail.php');
        global $aquarius;
        $node = or_die(
            db_Node::get_node($node),
            "Unable to load node '$node' for mail"
        );
        $smarty = $aquarius->get_smarty_frontend_container($GLOBALS['lg'], $node);

        $smarty->assign($vars);
        $mail = new AquaMail($smarty, $template.'.tpl', $template.'.html.tpl');

        $mail->set('from', $node->get_sender_email());
        $mail->set('replyto', $node->get_sender_email());

        return $mail;
    }
    
    /** Change account settings
      * Changing email address currently not supported, it's tricky and I'm lazy.
      * Expects fields password, password_confirm as request parameters
      * Changing password is only attempted when password1 is not empty */
    function account_change() {
        $pw = requestvar('password');
        $success = false;
        $errors = array();
        if (strlen($pw) > 0) {
            if ($pw == requestvar('password_confirm')) {
                $user = db_Fe_users::authenticated();
                $user->password = md5($pw);
                $user->update();
                $success = true;
            } else {
                $errors ['password']= 'usermanagement_passwords_dont_match';
            }
        }
        return compact('success', 'errors');
    }

    
    function new_password_mail($email, $link) {
        $errors = array();
        $success = false;
        
        $user = DB_DataObject::factory('fe_users');
        $user->name = trim($email);
        if (!$user->find()) {
            $errors['email'] = 'usermanagement_invalid_mail_address';
        } else {
            $user->fetch();

            $link->params['token'] = $this->create_token($user->name);

            $mail = $this->prepare_mail(
                    'mail_new_password',
                    'mail_new_password',
                    compact('link')
            );
            
            $mail->set('to', $user->name);
            
            $success = $mail->send();
        }
        return compact('success', 'errors');
    }
    
    function login_for_token($login_token) {
        $email = $this->parse_token($login_token);
        $user = DB_DataObject::factory('fe_users');
        $user->name = $email;
        $found = $user->find(true);
        if ($found) $user->login();
    }
    
    function change_password($user, $password, $password_confirm) {
        $errors = array();
        $success = false;
        
        if (strlen($password) < 1) {
            $errors['password'] = 'usermanagement_password_missing';
        }
        if ($password !== $password_confirm) {
            $errors['password_confirm'] = 'usermanagement_passwords_dont_match';
        }
        
        if (!$errors) {
            $user->password = md5($password);
            $success = $user->update();
            Log::info("Reset password for user $user->name($user->id)");
        }

        return compact('success', 'errors');
    }
    
    /** Load address for logged in user
      * Returns empty address if there is no user or no address for that user. */
    function get_address($user = false) {
        global $DB;
        if (!$user) $user = db_Fe_users::authenticated();
        $address = new Usermanagement_Address();
        if ($user) {
            $userid = false;
            if ($user instanceof db_Fe_Users) {
                $userid = $user->id;
            } else {
                $userid = $user;
            }
            $addresses = $DB->listquery("SELECT id FROM fe_address JOIN fe_user_address ON fe_address_id = id WHERE fe_user_id = ".mysql_real_escape_string($userid));
            if (count($addresses)) {
                $address->get(array_pop($addresses));
            }
        }
        return $address;
    }

    /** Return the currently logged in user, or false */
    function authenticated_user() {
        return db_Fe_users::authenticated();
    }

    /** Delete the account of the currently active user
      * (actually we just deactivate it) */
    function account_delete() {
        $user = db_Fe_users::authenticated();
        if ($user) {
            $user->active = false;
            $user->update();
        }
    }
    
    private function hmac($value, $expire) {
        return hash_hmac('sha1', $value.'/'.$expire, AQUARIUS_SECRET_KEY);
    }
    
    /** Generate a challenge token
      * The returned string can be used as a challenge, for example to verify that a user has access to an email account. Value and expire date will be part of the token.
      *  @param $value value to be stored in the token
      *  @param $expire How long (in seconds) the token will be valid, 24 hours by default
      *  @return token string
      * Valid tokens cannot be generated without the secretkey (according to my limited insight into cryptography) */
    private function create_token($value, $expire=86400) {
        $expire = time() + $expire;
        $expire_enc = dechex($expire);
        $value_enc = base64_encode($value);
        return $expire_enc.'-'.$value_enc.'-'.$this->hmac($value_enc, $expire_enc);
    }

    /** Get value from token
      * @return token value string if token is valid, false in all other cases */
    private function parse_token($token) {
        $parts = explode('-', $token);
        if (count($parts) != 3) return false;
        list($expire_enc, $value_enc, $hash) = $parts;
        $expire = hexdec($expire_enc);
        $value = base64_decode($value_enc);
        $verify_hash = $this->hmac($value_enc, $expire_enc);
        if ($verify_hash == $hash && $expire > time()) {
            return $value;
        }
        return false;
    }

}

/** Builds actions to add or edit user's addresses */
class Edit_Address_Action_Generator {
	function get_action($user) {
        if (!$user) return false;
		$user_address = DB_DataObject::factory('fe_user_address');
		$userid = $user;
		if ($user instanceof db_dataobject) {
            $userid = $user->id;
        }
        $user_address->fe_user_id = $userid;
        $found = $user_address->find(true);
		$cmd = false;
		$id = false;
		
		if($found == 0) {
              $cmd = 'add';
			  $id = $userid;
		}

		elseif($found > 0) {
			  $cmd = 'edit';
		      $id = $user_address->fe_address_id;
		}

		if($found > 1) {
			  Log::warn("$found found addresses for user $userid");
		}

		return Action::make('Fe_address', $cmd, $id);
	}
}
?>