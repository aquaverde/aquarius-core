<?php
/**
 * Table Definition for wording
 */

class db_Wording extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'wording';                         // table name
    public $lg;                              // char(2)  not_null primary_key
    public $keyword;                         // varchar(100)  not_null primary_key
    public $translation;                     // blob(65535)  not_null blob

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Wording',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
        
        static function getAllWordingsByLg($lg, $order="keyword") {
            $words = DB_DataObject::factory('wording');
            $words->orderBy(mysql_real_escape_string($order));
            $words->lg = $lg;
            $words->find();

            $result = array();
            while ( $words->fetch() )
                $result[] = clone($words);

            return $result;
        }
	
	static function getTranslation($key, $lg) {
        static $cache = array();
        if (!isset($cache[$key][$lg])) {
            $words = DB_DataObject::factory('wording');
            $words->lg = $lg;
            $words->keyword = $key;

            if ( $words->find()) {
                $words->fetch();
                $cache[$key][$lg] = $words->translation;
            } else {
                // The word has not been used yet, insert it for all languages and use key as default translation
                foreach(db_Languages::getLanguages(false) as $lang) {
                    $word = DB_DataObject::factory('wording');
                    $word->lg = $lang->lg;
                    $word->keyword = $key;
                    if (!$word->find()) {
                        $word->translation = $key;
                        $word->insert();
                    } else {
                        Log::debug("Wording $key exists for language {$lang->lg}");
                    }
                }
                $cache[$key][$lg] = $key;
            }
        }
        return $cache[$key][$lg];
	}
}
