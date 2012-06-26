<?php
/** @package Aquarius */


/**
 * Language definition. Note the convention (currently scarcely observed) of naming variables for raw language codes '$lg' and variables holding db_Languages objects '$lang' to distinguish between the two. Typically, you'd say $lg = 'fr'; $lang = db_Languages::staticGet($lg);
 */
class db_Languages extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'languages';                       // table name
    public $lg;                              // string(6)  not_null primary_key
    public $name;                            // string(150)  not_null
    public $weight;                          // int(10)  not_null multiple_key unsigned
    public $active;                          // int(1)  not_null multiple_key

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Languages',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /** List of all language instances
      * @param $require_active=false Set this to true if you want to use active languages only
      */
	static function getLanguages($require_active = false) {
        static $cache = array();
        if (!isset($cache[$require_active])) {
            $lang_prototype = DB_DataObject::factory('languages');
            $lang_prototype->orderBy('weight');

            if ( $require_active )
                $lang_prototype->active = true;
                
            $lang_prototype->find();
            
            $langs = array();
                        
            while ( $lang_prototype->fetch() )
                $langs[] = clone($lang_prototype);

            $cache[$require_active] = $langs;
        }
			
		return $cache[$require_active];
	}

    /** Validate that a language code references a valid and active language
      * @param $lg A thing assumed to be a language code string
      * @param $require_active whether language must be active to be valid (default true)
      * @return validated language code or false
      */
    static function validate_code($lg, $require_active=true) {
        $lang = new self();
        $lang->lg = $lg;
        if ($require_active) {
            $lang->active = true;
        }
        if ($lang->find(true)) {
            return $lang->lg;
        }
        return false;
    }
    
    /** Ensure that you have a valid language code reference.
      * Returns same language code if it's valid, or primary language.
      * @param $lg A thing assumed to be a language code string
      * @param DEPRECATED ignored
      * @return valid language code */
    static function ensure_code($lg) {
        $lg = self::validate_code($lg);
        if ($lg) return $lg;
        
        $lang = self::getPrimary();
        return $lang->lg;
    }


    /** Retrieve the primary language of the CMS */
    static function getPrimary() {
        // Retrieve the first active language (sorted by weight)
        $lang_prototype =& DB_DataObject::factory('languages');
        $lang_prototype->active = true;
        $lang_prototype->orderBy('weight');
        $lang_prototype->limit(0,1);
        if ( $lang_prototype->find() < 1 ) throw new Exception("No active language present");

        $lang_prototype->fetch();

        return $lang_prototype;
    }


	public function __toString() {
		return $this->lg;
	}
}
