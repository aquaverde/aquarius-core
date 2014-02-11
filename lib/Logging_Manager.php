<?php 
/** Logging is started based on two sources:
  * 1. Config entries in the 'log' section of the config
  * 2. Overrides sent by the 'aquarius_logging' cookie.
  *
  *
  */
class Logging_Manager {
    var $echokey;
    var $config;
    var $default_path;
    
    /** Create a logging manager
      * @param $echokey use this key to secure logging cookies
      * @param $config dict with configuration values
      * @param $default_path use this path as base path if the given path for
                             the logfile is relative.
      */
    function __construct($echokey, $config, $default_path) {
        $this->echokey = $echokey;
        $this->config  = $config;
        $this->default_path  = $default_path;
    }

    /** Load logger for given request
      *
      * @param $request request variables where we look for logging overrides
      *
      * The following config values are read:
      *  file:  What file to log to
      *  level: What log messages to log to file
      *  echolevel: What log messages to send out in response
      *  firelevel: what log messages to send in HTTP headers to be read by
      *             the FirePHP plugin.
      *
      * If a variable 'aquarius_logging' is set in $request, it is interpreted
      * to override logging behaviour for this request. If that variable is
      * invalid or expired, it is ignored without warning.
      *
      */
    function load_for($request) {
        $levels = array();
        $levels['file'] = $this->config['level'];
        $levels['echo'] = get($this->config, 'echolevel', LOG::NEVER);
        $levels['fire'] = get($this->config, 'firelevel', LOG::NEVER);

        $overridestr = get($request, 'aquarius_logging');
        $overrides = $this->parse_overrides($overridestr);

        if (!empty($overrides)) {
            $levels = array_merge($levels, $overrides);
        }

        $logfile = get($this->config, 'file');
        if ($logfile[0] !== '/') $logfile = $this->default_path.$logfile;
        return new Logger(
            $logfile,
            $levels['file'],
            $levels['echo'],
            $levels['fire']
        );
    }


    function parse_overrides($token) {
        $parts = explode(',', $token, 3);

        if (count($parts) !== 3) return false;

        $overridestr    = get($parts, 2, '');
        $timestamp      = intval(get($parts, 1, ''));
        $hmac           = get($parts, 0, '');
        if ($timestamp < time() || $timestamp > strtotime('+2 days')) return false;
        if ($this->hmac($overridestr, $timestamp) !== $hmac) return false;
        $overrides = json_decode($overridestr, true);
        if (!is_array($overrides)) return false;
        $overrides = array_filter(validate($overrides, array(
            'file' => 'int notset',
            'echo' => 'int notset',
            'fire' => 'int notset',
        )));
        return $overrides;
    }

    /** Override logging configuration by setting a cookie
      * @param $levels dict with loglevel entries 'file', 'echo', and 'fire'
      *
      * If $levels is empty, the cookie is cleared. The cookie will be valid for
      * two days.
      */
    function override_with_cookie($levels) {
        $levelstr  = json_encode($levels);
        $timestamp = time();
        $end_date  = strtotime('+2 days');
        $token = $this->hmac($levelstr, $end_date).",$end_date,$levelstr";
        setcookie('aquarius_logging', $token, $end_date, '/');
    }


    /** Create an HMAC that authenticates given logging override and timestamp */
    function hmac($override_str, $timestamp) {
        return hash_hmac('sha1', "$override_str,$timestamp", $this->echokey);
    }

}