<?php
/** @package Aquarius */

/** Frontend users
  * mainly used for access_restricted nodes
  */
class db_Fe_users extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fe_users';                        // table name
    public $id;                              // int(11)  not_null primary_key auto_increment group_by
    public $password;                        // varchar(255)  not_null
    public $name;                            // varchar(100)  not_null
    public $active;                          // tinyint(1)  not_null group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Fe_users',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	
	public $groups; // array of group ids, user is subscribed to
	
	
	function loadGroupRelations() {
		$this->groups = array();
		if ( $this->id != "" ) {
			$rel	= DB_DataObject::factory('fe_groups2user');
			$this->groups = $rel->getGroupsByUser($this->id);
		}
		return $this->groups;
	}

    /** List of all node ids that this user has access permission */
    function get_access_nodes() {
        global $DB;
        $id = intval($this->id);
        if ($id > 0) {
            return $DB->listquery("
                SELECT DISTINCT fe_restrictions.node_id
                FROM fe_users
                JOIN fe_groups2user ON fe_users.id = fe_groups2user.user_id
                JOIN fe_groups ON fe_groups2user.group_id = fe_groups.id
                JOIN fe_restrictions ON fe_groups2user.group_id = fe_groups.id
                WHERE fe_users.id = $id
                AND fe_groups.active = 1");
        } else {
            return array();
        }
    }
	
	function getActiveState() {
		if ( $this->active )
			return "on";
		else
			return "off";
	}
	
	/** Returns true if this user has access to the given node */
	function hasAccessTo($nodeid) {
        foreach($this->loadGroupRelations() as $group_id) {
            $group = DB_DataObject::factory('fe_groups');
            $group->get($group_id);
            if ($group->permitsAccessTo($nodeid))
                return true;
        }
        return false;
    }
    
    /** Delete this user and all group realations (DB_DataObject::delete override)*/
    function delete() {
        require_once("Fe_groups2user.php");
        db_Fe_groups2user::removeGroupsByUser($this->id);
        parent::delete(); // Call the DB_DataObject delete method
    }

    /** See whether the user is in the given group
      * @param $group_id id of the group
      * @return true if this user is in the group, false in all other cases */
    function in_group($group_id) {
        $rel = DB_DataObject::factory('fe_groups2user');
        $rel->user_id = $this->id;
        $rel->group_id = $group_id;
        return (bool)$rel->find();
    }
    
    /** Process frontend login requests.
      * Use values 'fe_username' and either 'fe_passwordhash' or 'fe_password' from $_REQUEST to authenticate an active frontend user. Creates fe_user instance and registers user in the session if successful.
       * If fe_passwordhash is used, it must be calculated with a function like this:
       *     function passwordhash(password, challenge) { return MD5(MD5(password)+challenge); }
       * where challenge is the session id. This allows clients to transmit the password digested with the session id, thereby obscuring the password and preventing the use in other sessions. (Though if someone can eavesdrop on the connection, e can always obtain the session id and try to hijack the session, but at least e can't obtain the password.)
       * If fe_passwordhash is not set, fe_password is expected to hold the plain-text password.
       * @return user instance if login is successful, -1 if login failed, false if no login credentials were found.
       */
    static function authenticate() {
        $fe_username = requestvar('fe_username');
        if ($fe_username) {
            $fe_passwordhash = requestvar('fe_passwordhash');
            $fe_password = requestvar('fe_password');
            $userproto = DB_DataObject::factory('fe_users');
            $userproto->name = $fe_username;
            $userproto->active = 1;
            
            $db = $userproto->getDatabaseConnection();
            if (strlen($fe_passwordhash) > 0)
                $userproto->whereAdd("MD5(CONCAT(password, '".session_id()."')) = '".$db->escapeSimple($fe_passwordhash)."'"); // Obscured passwords
            else
                $userproto->whereAdd("password = MD5('".$db->escapeSimple($fe_password)."')"); // clear-text transmitted passwords
            $found = $userproto->find();
            if ($found == 1) {
                $userproto->fetch();
                Log::info("User name '$userproto->name' authenticated, has id $userproto->id");

                $userproto->login();

                return $userproto;
            } else {
                Log::info("Couldn't authenticate user name '$userproto->name'");
                return -1;
            }
        }
        return false;
    }
    
    /** Loads user instance from session if the user authenticated himself already */
    static function authenticated() {
        global $aquarius;
        $user_id = $aquarius->session_get('fe_user_id');
        if ($user_id) {
            $user = new self();
            $user->id = $user_id;
            if ($user->find(true) && $user->active) {
                return $user;
            } else {
                self::logout();
                throw new Exception("Invalid user id '$user_id' in session");
            }
        }
        return false;
    }

    /** Register this user as logged in */
    function login() {
        global $aquarius;
        $aquarius->session_set('fe_user_id', $this->id);
        session_regenerate_id();
    }

    /** Clear the user id from session */
    static function logout() {
    	global $aquarius;
    	$user_id = $aquarius->session_get('fe_user_id') ;
    	Log::info("Logout fe_user: ".$user_id);
    	$aquarius->session_set('fe_user_id', NULL) ;
    }
    
}
