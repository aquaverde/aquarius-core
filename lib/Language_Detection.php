<?php 

/** Determine language from environment (URI, configuration, HTTP cookies, &c.)
  * The process is componentized into detectors so it can easily extended by modules.
  * Detector components are callback functions that take request parameters as input and either return a language code or nothing.
  *
  * @param $detector_names is an optional list of builtin detector names to add.
  * */
class Language_Detection {
    private $language_detectors;

    /** Create a language detection process
    * @param $detector_names is an optional list of builtin detector names to add.
    * */
    function __construct($detector_names = array()) {
        $this->language_detectors = new Named_List;
        foreach($detector_names as $detector_name) $this->add_detector($detector_name);
    }

    /** Register a language detection mechanism
      * When no $detector is given, this class' method with the same $name is used.
      */
    function add_detector($name, $detector = null, $location = 'after', $relative_to = null) {
        if (!$detector) {
            $detector = array('Language_Detection', $name);
        }
        if (!is_callable($detector)) {
            throw new Exception("Detector '$name' not callable");
        }
        $this->language_detectors->add($name, $detector, $location, $relative_to);
    }

    /** Run language detection
      * All registered detectors are queried in order. Returned values are checked for validity. The first valid language code is returned.
      */
    function detect($parameters) {
        foreach($this->language_detectors as $name => $detector) {
            Log::debug("Trying language detection by $name");
            $proposed_lg = call_user_func($detector, $parameters);
            if ($proposed_lg) {
                // Make sure that language is valid
                $lg = db_Languages::validate_code($proposed_lg, get($parameters, 'require_active'));
                if ($lg) {
                    Log::debug("Language '$lg' detected by $name");
                    return $lg;
                } else {
                    Log::debug("Language '$proposed_lg' proposed by $name not usable");
                }
            }
        }
    }

    /** Detect language by request parameter 'lg' */
    static function request_parameter($params) {
        return get($params['request'], 'lg');
    }

    /** Use the first path part if it's a valid language code  */
    static function request_path($params) {
        // Try to use the first path part as language code
        return array_shift(array_filter(explode('/', $params['uri']->path)));
    }

    /** Detect language by domain */
    static function domain($params) {
        return $params['domain_conf']->get($params['uri']->host, 'lg');
    }

    /** Detect language by HTTP_ACCEPT_LANGUAGE header sent by browser */
    static function accepted_languages($params) {
        $accepted_languages = explode(",", get($params['server'], 'HTTP_ACCEPT_LANGUAGE'));

        foreach ($accepted_languages as $langstr) {
           $lg = db_Languages::validate_code(substr($langstr, 0,2), get($params, 'require_active'));
           if ($lg) return $lg;
        }
    }

    /** Always return the primary language */
    static function primary($params) {
        return db_Languages::getPrimary()->lg;
    }
}
