<?php 

/** LAnguage detection adapted to select between the available admin interface translations
  * */
class Language_Detection_Admin extends Language_Detection {
    var $available_languages = array('en', 'de', 'fr');
    
    /** Run language detection
      * All registered detectors are queried in order. Returned values are checked for validity. The first valid language code is returned.
      */
    function detect($parameters) {
        foreach($this->language_detectors as $name => $detector) {
            Log::debug("Trying language detection by $name");
            $lg = call_user_func($detector, $parameters);
            if ($lg) {
                if (in_array($lg, $this->available_languages)) {
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


    /** Detect language by HTTP_ACCEPT_LANGUAGE header sent by browser
      * Accept languages where a translation of the admin interface is available */
    static function accepted_languages($params) {
        $accepted_languages = explode(",", get($params['server'], 'HTTP_ACCEPT_LANGUAGE'));

        foreach ($accepted_languages as $langstr) {
            $accepted_lg = substr($langstr, 0,2);
            if (in_array($accepted_lg, $this->available_languages)) return $accepted_lg;
        }
    }
    
    /* Determine base language to use in backend */
    function user_default_lang($params) {
        $user = get($params, 'user');
        if ($user) return $user->defaultLanguage;
    }
    
    /* Use default admin language */
    function use_default() {
        return first($this->available_languages);
    }
}
