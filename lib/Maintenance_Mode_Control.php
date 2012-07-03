<?php
/** Enable, disable, and check for maintenance mode.
  *
  * Some operations (installation/updates) cannot rely on the authentiaction 
  * mechanisms in Aquarius because standard authentication might not be
  * available when they're being executed. To achieve an acceptable level of
  * security on otherwise public scripts, these operations rely on maintenance-
  * mode.
  *
  * Maintenance mode may be enabled by creating a special file. There is a
  * relatively secure method, and a casual method for occasions where you are
  * developing on a local system (or you don't care).
  * 
  * The quite secure way requires that:
  *   - the file 'AQUARIUS_MAINTENANCE' exists in the install-directory and contains
  *     three strings separated by comma
  *
  *   - time limit: the first string is an UTC timestamp of the form YY.mm.dd hh:mm.
  *     The specified timestamp lies in the future, but no more than 24h.
  *
  *   - host: the second value must match the string '*' or PHP's 
  *     $_SERVER['REMOTE_ADDR']
  *
  *   - cookie: the third value must match the string '*' or the request
  *     variable 'aquarius_maintenance'
  * 
  * The timestamp is mandatory to avoid unlimited open doors. Whitespace is
  * trimmed.
  * 
  * The casual way requires that:
  *   - a file 'AQUARIUS_MAINTENANCE.CASUAL' exists in the install directory
  * 
  *   - the file's change-date is less than two hours old.
  * 
  * Example contents for AQUARIUS_MAINTENANCE:
  *   Permit all clients between the 2010.05.11 13:14 and 2010.05.12 13:14:
  *     2010.05.12 13:14, *, *
  *
  *   Client must connect from address 10.1.1.1
  *     2010.05.12 13:14, 10.1.1.1, *
  *
  *   Permit for clients providing key '45wyrthfgcbv'
  *     2010.05.12 13:14, *, 45wyrthfgcbv
  */
class Maintenance_Mode_Control {
    const auth_file_name = 'AQUARIUS_MAINTENANCE';
    const casual_file_name = 'AQUARIUS_MAINTENANCE.CASUAL';

    static private function auth_file($casual=false) {
        $dir = realpath(dirname(__FILE__).'/../..').'/';
        $file = $dir.($casual ? self::casual_file_name : self::auth_file_name);
        return $file;
    }

    /** Determine whether maintenance mode is active.
      * @return an array with status code and reason string
      * The returned status code is one of -1 (mode not active), 0 (mode active but not for this request), and 1 (mode active for this request).
      * Only status 1 indicates that the user agent should be allowed to start maintenance operations. */
    static function validate() {
        $casual_file = self::auth_file(true);
        if (file_exists($casual_file)) {
            $cdate   = filemtime($casual_file);
            if (is_numeric($cdate) && $cdate > strtotime('-2 hours')) {
                return array(1, "Casual maintenance mode active.");
            }
        }
    
        $auth_file = self::auth_file();
        if (!file_exists($auth_file)) {
            return array(-2, self::auth_file_name." not set.");
        }

        $secrecstr = file_get_contents($auth_file);
        if (empty($secrecstr)) {
            return array(-1, "Unable to read ".self::auth_file_name." or file empty.");
        }

        $secrecs = array_filter(array_map('trim', explode(',', $secrecstr)));
        if (count($secrecs) != 3) {
            return array(-1, self::auth_file_name." format error");
        }

        $date = parse_date($secrecs[0], '%Y.%m.%d %H:%M');
        if (!$date)                      return array(-1, "Date format error.");
        if ($date < time())              return array(-1, "Setup time is over.");
        if (strtotime('+1 day', gmdate('U')) < $date) return array( 0, "Setup time is not here yet.");

        if ($secrecs[1] !== '*' && $secrecs[1] !== $_SERVER['REMOTE_ADDR']) {
            return array(0, 'Host '.$_SERVER['REMOTE_ADDR'].' not permitted.');
        }

        if ($secrecs[2] !== '*' && $secrecs[2] !== get($_COOKIE, 'AQUARIUS_MAINTENANCE')) {
            usleep(500000); // Wait half a second until we tell about invalid keys
            return array(0, 'Setup key not valid.');
        }
        
        return array(1, "Maintenance mode active for this request");
    }


    /** Is maintenance mode active for this request?
      * If it isn't for some reason, an exception is thrown. */
    static function check() {
        list($code, $reason) = self::validate();
        if ($code < 1) throw new Exception($reason);
    }


    /** Start maintenance mode for this client */
    static function enable($hours) {
        $host = $_SERVER['REMOTE_ADDR'];
        $key  = base_convert(mt_rand(0x1679616, 0x39AA3FF), 10, 36);

        $hours = min(24, max(1, intval($hours)));
        $end_date = strtotime("+$hours hours", gmdate('U'));
        $datestr = strftime('%Y.%m.%d %H:%M', $end_date);

        setcookie('AQUARIUS_MAINTENANCE', $key, $end_date, '/');

        $auth_file = self::auth_file();
        $auth_string = "$datestr, $host, $key";
        $result = file_put_contents($auth_file, $auth_string);
        if (!$result) throw new Exception("Unable to write '$auth_string' to $auth_file");

        return compact('host', 'key', 'end_date', 'datestr');
    }


    static function disable() {
        $auth_file = self::auth_file();
        if (file_exists($auth_file)) {
            $success = unlink($auth_file);
            if (!$success) {
                // Try resetting
                $success = file_put_contents($auth_file, '');
                if (!$success) throw new Exception("Unable to delete file $auth_file");
            }
            return true;
        }
    }
}
