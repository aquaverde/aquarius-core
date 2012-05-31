<?php
/**
    Serve files from known locations.
    Currently available locations:
        js:    For javascript files in 'templates/js', these files are interpreted with PHP so that <?=?> tags may be used
        jslib: For javascript files in 'templates/js/lib'. Mainly for third-party libraries. These are delivered as-is.
        css: For admin css files in 'templates/css'. Mainly for third-party libraries. These are delivered as-is.

    The 'js' location requires a logged in user because the files are interpreted, whereas 'jslib' may be accessed without login. The location 'css' is interpreted but available for clients not logged in (this means that css templates can use configuration constants).

    Note that files may be overriden in modules. If modules include a file at the same location, it will be used instead of the original aquarius files. (Without doubt, this will feature will lead to confusion in some occasions, and it's better used sparingly. But this way is better that making site-specific changes in the aquarius dir.)

    This class is made for occasions where you know exactly what you want. It throws an exception in any case where the location or the file is unknown. Please do not catch silently.
*/
class action_file extends Action implements SideAction {

    var $props = array('class', 'location', 'file');

    static $known_locations = array(
        'js' => array(
            'path' => 'templates/js/',
            'type' => 'text/javascript',
            'parse' => true,
            'require_login' => true),
        'jslib' => array(
            'path' => 'templates/js/lib/',
            'type' => 'text/javascript',
            'parse' => false,
            'require_login' => false),
        'css' => array(
            'path' => 'templates/css/',
            'type' => 'text/css',
            'parse' => true,
            'require_login' => false)
    );

    /** Build file load action
      * Adds the file's last modification time to the url to allow for mindless caching
      */
    static function make($location, $file) {
        $known_location = self::get_location($location);
        $filepath = self::get_realpath($known_location, $file);
        $realfile = basename($filepath);
        $action = Action::make('file', $location, $realfile);

        // Overwrite action sequence number with file modification time.
        // This is done for one reason, caching:
        // 1. Keep the URL the same if the file didn't change so that browsers cache it
        // 2. Change the URL if the file did change, which ensures that user agents always use the current copy
        $action->sequence = filemtime($filepath);

        return $action;
    }

    /** Check that a user is logged in or that the location does not require authentication */
    function permit() {
        $location = self::get_location($this->location);

        if ($location['require_login'] && !db_Users::authenticated()) return false;

        return true;
    }

    function process($aquarius, $request) {
        $location = $this->get_location($this->location);
        $filepath = $this->get_realpath($location, $this->file);

        header('Content-Type: '.$location['type']);

        // Tell the agent that this response is valid forever so that it caches the file
        // Since we use the file modification time as sequence number which shows up in the URL, if the file changes the URL will change as well.
        header("Date: ".gmdate("D, d M Y H:i:s", time())." GMT");
        header("Expires: ".gmdate("D, d M Y H:i:s", time() + 60 * 60 * 24 * 360)." GMT"); // One year in the future is the maximum allowed expiry time (RFC2616).

        while(@ob_end_clean()); // Throw away all output buffers, we want to deliver the file only

        if ($location['parse']) {
            $success = include $filepath;
        } else {
            $success = readfile($filepath, true);
        }

        if (!$success) throw new Exception("Failed including $filepath");
    }

    private static function get_location($location) {
        $known_location = get(self::$known_locations, $location);
        if (!$known_location) throw new Exception("Unknown location '$location'");
        return $known_location;
    }

    private static function get_realpath($known_location, $file) {
        global $aquarius;
        $file = basename($file); // Remove any path information to avoid the infamous '..' hack
        foreach(explode(PATH_SEPARATOR, get_include_path()) as $root_path) {
            $candidate_path = $root_path.$known_location['path'].$file;
            if (@file_exists($candidate_path)) { // The infamous PHP open_basedir restriction generates a warning when we access stuff outside our confines, hence the @
                $path = realpath($candidate_path);
            }
        }
        if (!$path) throw new Exception("No file $file in ".$known_location['path']);
        return $path;
    }
}
