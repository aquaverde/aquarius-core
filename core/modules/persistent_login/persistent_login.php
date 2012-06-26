<?
/** Module to keep people (well, their user-agent) logged into frontend.
  * This is done by setting a long-term cookie 'aquarius_fe_login' when a user
  * logs in; and by refreshing that cookie on each new visit to the site.
  *
  * The cookie stores three values:
  *   * a frontend user-id integer
  *   * a termination date as UNIX-timestamp
  *   * the HMAC of the other two values combined with SECRET_KEY
  *
  * When a login is attempted, this module checks for a valid cookie and logs
  * the user in automatically. When a user logs-in the normal way, the cookie
  * is created. When a user logs out, an empty cookie is sent to override
  * leftover cookies.
  */
class Persistent_Login extends Module {
    var $short = "persistent_login";
    var $register_hooks = array(
        'frontend_node',
        'frontend_login',
        'frontend_logout'
    );

    var $cookie_name = 'aquarius_fe_login';

    /** Send the user a persistent cookie on login success */
    function frontend_login($user) {
        $this->persist_user($user);
    }
    

    /** Check for a cookie that authenticates the user */
    function frontend_node() {
        $auth_str = get($_COOKIE, $this->cookie_name);

        // Only try to use the cookie if user is not logged in
        if ($auth_str && !db_Fe_users::authenticated()) {
            Log::debug("Trying to authenticate using auth_str '$auth_str'");
            $user_id = $this->parse($auth_str);
            if ($user_id) {
                $user = db_Fe_users::staticGet($user_id);
                if ($user and $user->active) {
                    Log::info("Logging in user $user_id based on auth_str '$auth_str'");
                    $user->login();

                    // Renew cookie for user
                    $this->persist_user($user);
                } else {
                    Log::info("User $user_id in auth_str '$auth_str' is not active");
                }
            } else {
                Log::debug("Invalid auth_str '$auth_str'");
            }
        }
    }

    /** Remove the persistent cookie when a user logs out */
    function frontend_logout() {
        Log::debug("Removing cookie $this->cookie_name");
        $this->cook(false, 0);
    }

    /** Send a cookie with authentication information to the user-agent. */
    function persist_user($user) {
        $termination_date = strtotime('+'.$this->conf('duration'));
        $auth_str = $this->login_auth_str($user->id, $termination_date);
        Log::debug("Setting persistent login cookie $this->cookie_name=$auth_str");
        $this->cook($auth_str, $termination_date);
    }

    function parse($auth_str) {
        $parts = explode('-', $auth_str);
        $filtered_parts = validate($parts, array(0=>'int', 1=>'int'));
        if (count($filtered_parts) != 2) return false;
        $user_id = get($filtered_parts, 0);
        $date = get($filtered_parts, 1);
        
        // the date must lie in the future and regenerating an auth_string must
        // yield the same string
        if (  $date > time()
           && $auth_str == $this->login_auth_str($user_id, $date)
        ) {
            return $user_id;
        }
        return false;
    }

    function login_auth_str($user_id, $date) {
        $cstr = "$user_id-$date";
        return $cstr.'-'.hash_hmac("sha256", $cstr, AQUARIUS_SECRET_KEY);
    }
    
    function cook($value, $date) {
        setcookie($this->cookie_name, $value, $date, '/', false, false, true);
    }
}