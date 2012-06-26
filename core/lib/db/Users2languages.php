<?php
/**
 * Table Definition for users2languages
 */

class db_Users2languages extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'users2languages';                 // table name
    public $id;                              // int(11)  not_null primary_key auto_increment
    public $userId;                          // int(11)  not_null multiple_key
    public $lg;                              // string(6)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Users2languages',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	
	static function deleteUser($userId) {
		$proto =& DB_DataObject::factory('users2languages');
		$proto->userId = $userId;
		$proto->find();
		
		while ( $proto->fetch() )
			$proto->delete();
	}
	
	static function addUsersLanguage($userId, $lg) {
		$proto =& DB_DataObject::factory('users2languages');
		$proto->userId = $userId;
		$proto->lg = $lg;
		$proto->insert();
	}
	
        /** Get the list of available languages for the given user
          * @return hash of language->lg, language->name
          */
	static function getLanguagesForUser($user) {
            DB_DataObject::factory('languages'); // To ensure languages class is loaded
            
            $langs = array();
            
            // Siteadmins may access any language
            if ($user->isSuperadmin() || $user->isSiteadmin() ) {
                $langs = db_Languages::getLanguages();
            } else {
                $proto =& DB_DataObject::factory('users2languages');
                $proto->userId = $user->id;
                $found = $proto->find();
                while ( $proto->fetch() ) {
                    $lang = new db_Languages();
                    $lang->lg = $proto->lg;
                    if ($lang->find(true)) $langs []= $lang;
                }
            }
                    
            // Map list of lanuages to lg=>name hash
            $result = array();
            foreach($langs as $lang) $result[$lang->lg] = $lang->name;
            return $result;
	}
}