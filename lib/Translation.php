<?php 
/** All the below classes have a toString() method which yields a translation string */

/** Use config translation variable
  * Allow passing of values to be used in format strings */
class Translation {

    var $key;
    var $replacements;

    static $configvars = null; // Cache smarty config vars used for translation

    /** Lookup translation in smarty config
      * @param $key The key of the translation string
      * @param $default optional default value if no translation available
      */
    static function for_key($key, $default=null) {
        if(self::$configvars == null) {
            global $aquarius;
            self::$configvars = $aquarius->get_smarty_backend_container()->get_config_vars();
        }

        $translation = get(self::$configvars, $key, $default);
        if ($translation === null) {
            Log::warn("Missing translation for key '$key'");
            $translation = '';
        }
        return $translation;
    }

    /** Create translation instance
      * @param $key the key of the translation string
      * @param $replacements printf translation string with values from this array */
    function __construct($key, $replacements=false) {
        $this->key = $key;
        $this->replacements = $replacements;
    }

    public function __toString() {
        $translation = self::for_key($this->key);
        if(empty($this->replacements)) {
            return $translation;
        } else {
            return vsprintf($translation, $this->replacements);
        }
    }
}

/** Used to fool legacy code */
class FixedTranslation {

    var $string;

    function __construct($string) {
        $this->string = $string;
    }
    
    public function __toString() {
        return $this->string;
    }
}

/** Use wordign tables to get a translation */
class WordingTranslation {

    var $key;
    var $replacements;
    var $translation;

    function __construct($key, $replacements=array()) {
        $this->key = $key;
        $this->replacements = $replacements;
    }
    
    public function __toString() {
        if ($this->translation === null) {
            global $lg;
            $this->translation = db_Wording::getTranslation($this->key, $lg);
            if (!empty($this->replacements)) $this->translation = vsprintf($this->translation, $this->replacements);
        }
        return $this->translation;
    }
}
