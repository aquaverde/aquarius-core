<?php
/** Provide frontend user management functions
  *
  * From the user's perspective, the process to register a new user looks as follows:
  *   1. Send account request via web form
  *   2. recieve verification email, click contained link
  *
  * Usually, it would be enough to mail a verification token to the user's mail address. A user entry will be created when the user can produce the token. This is not enough in our scenario since the user may give his full address on signup. This address must be stored until the user verified the token. The following process was chosen:
  *
  *   PERSONAE:
  *    DB: the database
  *    SITE: the server-side code
  *    CLIENT: User with HTTP/SMTP User agent
  *
  *        SITE <- CLIENT  account request for address $a
  *  DB <- SITE            store $a
  *  DB -> SITE            stored address with id $i
  *        SITE            create token $t for $i
  *        SITE -> CLIENT  send $t over SMTP mail
  *        SITE <- CLIENT  recieved $t
  *        SITE            verify $t, extract $i
  *  DB <- SITE            create user for address $i
  *  DB -> SITE            created user with id $ui
  *        SITE            log-in user $ui
  */
class Fe_user_management extends Module {
    var $register_hooks = array('frontend_page', 'smarty_config_frontend', 'smarty_config_backend', 'feuser_list_filteractions', 'feuser_list_actions');
    
    var $short = "fe_user_management";
    var $name  = "Frontend User Management";

    /** assoc array with results of operations such as account_request */
    var $results = array();

    function frontend_interface() {
        return $this;
    }

    /** Look for and execute requests */
    function frontend_page($smarty) {
        if (isset($_REQUEST['account_request'])) $this->account_request();
        if (isset($_REQUEST['account_confirm'])) $this->account_confirm();
        if (isset($_REQUEST['account_change'])) $this->account_change();
        if (isset($_REQUEST['password_request'])) $this->password_request();
        if (isset($_REQUEST['password_reset'])) $this->password_reset();
        if (isset($_REQUEST['save_address'])) $this->save_address();
    }

    function feuser_list_filteractions() {
        return Action::make('fe_address', 'export', false);
    }

    function feuser_list_actions() {
        return new Edit_Address_Action_Generator();
    }

    /** get the result of an operation
      * params:
      *   operation: name of the operation
      * False is returned if there are no results, likely because the operation was not executed */
    function get_result($params) {
        return get($this->results, get($params, 'operation'), false);
    }

    /** Save address and send out an authentication token to the user
      * The address is saved to the DB. With the id of the address a challenge token is built and sent to the user by email. */
    function account_request() {
        $mail_address = trim(requestvar('email'));
        $error = false;
        $sent = false;

        if (!$this->valid_mail_address($mail_address)) {
            $error = "usermanagement_invalid_email_Address";
        }

        // Ensure user doesn't exist already
        if (!$error) {
            $user = DB_DataObject::factory('fe_users');
            $user->name = $mail_address;
            if ($user->find()) {
                $error = "usermanagement_user_exists";
            }
        }

        // Save address to DB, send challenge token by mail
        if (!$error) {
            $address_id = $this->save_address();
            $challenge = $this->create_token($address_id);

            global $aquarius;
            $smarty = $aquarius->get_smarty_frontend_container();
            $smarty->caching = false;
            $smarty->assign('lg', $GLOBALS['lg']);
            $smarty->assign('address', $mail_address);
            $smarty->assign('challenge', $challenge);
            $mail = new AquaMail($smarty, 'user_management.mail.account_request.tpl', 'user_management.mail.account_request.html.tpl');
            $mail->set('to', $mail_address);
            $sent = $mail->send();
            if (!$sent) $error = "usermanagement_failed_sending_email";
        }
        
        $sent = !$error;
        $this->results['account_request'] = compact('sent', 'mail_address', 'error');
    }
    
    /** Create an account for a user.
      * The token in the 'account_confirm' request variable is read and validated.
      * If the token is valid, either a user account for the name contained in the token is created or the user's password is reset if the user exists already
      *  */
    function account_confirm() {
        $error = false;

        $token = requestvar('challenge');
        $address_id = $this->parse_token($token);

        if (!$address_id) $error = 'usermanagement_invalid_token';

        // Load address from DB
        $address = false;
        if (!$error) {
            $address = DB_DataObject::factory('fe_address');
            $found = $address->get($address_id);
            if (!$found) throw new Exception("Encountered valid token with address id '$address_id' but no corresponding DB entry");
        }

        // Prepare user object for this address
        $user = false;
        if (!$error) {
            $user = DB_DataObject::factory('fe_users');
            $user->name = $address->email;
            if ($user->find()) $error = 'usermanagement_user_exists';
        }

        // Generate password, save to DB
        if (!$error) {
            $password = $this->generate_password();
            $user->password = md5($password);
            $user->active = true;
            $user->insert();
            Log::info("Added user $user->name($user->id)");
            
            // Add user to default groups
            foreach(explode(',', FE_USER_MANAGEMENT_AUTOGROUPS) as $group_id) {
                if (!$user->in_group($group_id)) {
                    $rel = DB_DataObject::factory('fe_groups2user');
                    $rel->user_id = $user->id;
                    $rel->group_id = $group_id;
                    $rel->insert();
                }
            }
            
            // Join address to user
            $user_address = DB_DataObject::factory('fe_user_address');
            $user_address->fe_user_id = $user->id;
            $user_address->fe_address_id = $address->id;
            $user_address->insert();
            
            // Log him in already
            $_SESSION['fe_user_id'] = $user->id;
        }

        // Send mail
        if (!$error) {
            global $aquarius;
            $smarty = $aquarius->get_smarty_frontend_container();
            $smarty->caching = false;
            $smarty->assign('lg', $GLOBALS['lg']);
            $smarty->assign('address', $user->name);
            $smarty->assign('password', $password);
            $mail = new AquaMail($smarty, 'user_management.mail.account_confirm.tpl', 'user_management.mail.account_confirm.html.tpl');
            $mail->set('to', $user->name);
            $sent = $mail->send();
            if (!$sent) $error = "usermanagement_failed_sending_email";
        }
        
        $this->results['account_confirm'] = compact('address', 'error');
    }
    
    /** Change account settings
      * Changing email address currently not supported, it's tricky and I'm lazy.
      * Expects fields password, password_confirm as request parameters
      * Changing password is only attempted when password1 is not empty */
    function account_change() {
        $pw = requestvar('password');
        if (strlen($pw) > 0) {
            $success = false;
            if ($pw == requestvar('password_confirm')) {
                $user = db_Fe_users::authenticated();
                $user->password = md5($pw);
                $user->update();
                $success = true;
            } else {
                $error = 'usermanagement_passwords_dont_match';
            }
            $this->results['account_confirm'] = compact('success', 'error');
        }
    }

    /** Sends a mail with a token that lets user reset his password to address in request variable 'email' */
    function password_request() {
        $error = false;
        $address = requestvar('email');
        if (!$this->valid_mail_address($address)) {
            $error = "usermanagement_invalid_email_address";
        }
        
        if (!$error) {
            $user = DB_DataObject::factory('fe_users');
            $user->name = $address;
            if (!$user->find()) $error = 'usermanagement_no_such_user';
        }
        
        if (!$error) {
            $challenge = $this->create_token($address);

            global $aquarius;
            $smarty = $aquarius->get_smarty_frontend_container();
            $smarty->caching = false;
            $smarty->assign('lg', $GLOBALS['lg']);
            $smarty->assign('address', $address);
            $smarty->assign('challenge', $challenge);
            $mail = new AquaMail($smarty, 'user_management.mail.password_request.tpl', 'user_management.mail.password_request.html.tpl');
            $mail->set('to', $address);
            $sent = $mail->send();
            if (!$sent) $error = "usermanagement_failed_sending_email";
        }
        $this->results['password_request'] = compact('error');
    }

        /** Expects a token with mail address (user name) in request variable 'challenge'
          * Resets user's password and mails new credentials. */
    function password_reset() {
        $error = false;
                
        $token = requestvar('challenge');
        $address = $this->parse_token($token);

        if (!$address) $error = 'usermanagement_invalid_token';
        
        if (!$error) {
            $user = DB_DataObject::factory('fe_users');
            $user->name = $address;
            if (!$user->find()) $error = 'usermanagement_no_such_user';
            else $user->fetch();
        }
        
        // Generate password, save to DB, log in
        if (!$error) {
            $password = $this->generate_password();
            $user->password = md5($password);
            $user->update();
            Log::info("Reset password for user $user->name($user->id)");
            
            $_SESSION['fe_user_id'] = $user->id;
        }

        if (!$error) {
            global $aquarius;
            $smarty = $aquarius->get_smarty_frontend_container();
            $smarty->caching = false;
            $smarty->assign('lg', $GLOBALS['lg']);
            $smarty->assign('address', $address);
            $smarty->assign('password', $password);
            $mail = new AquaMail($smarty, 'user_management.mail.password_reset.tpl', 'user_management.mail.password_reset.html.tpl');
            $mail->set('to', $address);
            $sent = $mail->send();
            if (!$sent) $error = "usermanagement_failed_sending_email";
        }
        $this->results['password_reset'] = compact('error');
    }
    
    /** Load address for logged in user
      * Returns empty address if there is no user or no address for that user. */
    function get_address() {
        global $DB;
        $user = db_Fe_users::authenticated();
        $address = DB_DataObject::factory('fe_address');
        if ($user) {
            $addresses = $DB->listquery("SELECT id FROM fe_address JOIN fe_user_address ON fe_address_id = id WHERE fe_user_id = ".mysql_real_escape_string($user->id));
            if (count($addresses)) {
                $address->get(array_pop($addresses));
            }
        }
        return $address;
    }

    /** Read request variables and save them to address table
      * The address of logged in users will be overwritten, else a new entry is created.
      * Returns id of the address entry. */
    function save_address() {
        $address = $this->get_address();
        $fields = array('firstname', 'lastname', 'firma', 'address', 'zip', 'city', 'country', 'phone', 'email');
        foreach($fields as $field) {
            $address->$field = requestvar($field);
        }
        if (!$address->id) {
            $address->insert();
            $user = db_Fe_users::authenticated();
            if ($user) {
                $user_address = DB_DataObject::factory('fe_user_address');
                $user_address->fe_user_id = $user->id;
                $user_address->fe_address_id = $address->id;
                $user_address->insert();
            }
        } else {
            $address->update();
        }
        return $address->id;
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

    private function generate_password() {
        require_once('lib/libpwgen.php');
        $pwgen = new PWGen(8,8);
        return $pwgen->getPasswd();
    }
    
    private function hmac($value, $expire) {
        return sha1(FE_USER_MANAGEMENT_KEY.'/'.$value.'/'.$expire);
    }
    
    /** Generate a challenge token
      * The returned string can be used as a challenge, for example to verify that a user has access to an email account. Value and expire date will be part of the token.
      *  @param $value value to be stored in the token
      *  @param $expire How long (in seconds) the token will be valid, 24 hours by default
      *  @return token string
      * Valid tokens cannot be generated without the FE_USER_MANAGEMENT_KEY (according to my limited insight into cryptography) */
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
    
    /** Ensure a string looks remotely like a plain mail address
      * This method does not fully comply with the RFC, but it should be liberal enough to accept the worst addresses still in use. Quoted local parts are not accepted. */
    private function valid_mail_address($address) {
        // Yes, even braces '{}' are allowed in local parts of mail addresses
        return eregi('^[-a-z0-9!#$%&*+/=?^_`{|}~.]+@([-a-z0-9]+\.)+[-a-z0-9]+$', $address);
    }
}

class Edit_Address_Action_Generator {
	function get_action($user) {
		$user_address = DB_DataObject::factory('fe_user_address');
        $user_address->fe_user_id = $user->id;
        $found = $user_address->find(true);
		$cmd = false;
		$id = false;
		
		if($found == 0) {
              $cmd = 'add';
			  $id = $user->id;
		}

		elseif($found > 0) {
			  $cmd = 'edit';
		      $id = $user_address->fe_address_id;
		}

		if($found > 1) {
			  Log::warn("$found found addresses for user $user->id");
		}

		return Action::make('Fe_address', $cmd, $id);
	}
}
