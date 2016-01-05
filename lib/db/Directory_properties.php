<?php
/**
 * Table Definition for directory_properties
 */

class db_Directory_properties extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'directory_properties';            // table name
    public $directory_name;                  // varchar(765)  not_null primary_key
    public $resize_type;                     // char(3)  not_null
    public $max_size;                        // int(5)  group_by
    public $th_size;                         // int(4)  group_by
    public $alt_size;                        // int(4)  group_by

    /* Static get */
    static function staticGet($k,$v=NULL, $dummy=NULL) { return DB_DataObject::staticGet('db_Directory_properties',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    
    /** Retrieve directory properties
    * Defaults are substituted for missing settings. If there is no entry in the DB, everything will default.
    * @param $path path to directory, relative from FILEBASEDIR
    * @param $use_defaults whether to substitute defaults for missing settings, they will be zero otherwise (on by default)
    * @return Whether there was a DB entry. */
    function load($path, $use_defaults = true) {
        // Sanitize the path for DB lookup
        while (substr($path, 0, 1) == '/') $path = substr($path, 1); // Remove prefixed slashes
        while (substr($path, -1, 1) == '/') $path = substr($path, 0, -1); // Remove postfixed slashes

        // Make sure the caller knows what he's doing
        $absolute_path = FILEBASEDIR.$path;
        if (!is_dir($absolute_path)) throw new Exception("'$absolute_path' is not a directory'");
        
        // Load properties from DB
        $loaded = $this->get('directory_name', $path); // This may or may not have loaded an entry from the DB

        if ($use_defaults) {
            // Substitute the default value where missing
            if (empty($this->resize_type)) $this->resize_type = PICTURE_RESIZE;
            if (empty($this->th_size)) $this->th_size = PICTURE_TH_SIZE;
            if (empty($this->alt_size)) $this->alt_size = PICTURE_ALT_SIZE;
            if (empty($this->max_size)) $this->max_size = PICTURE_MAX_SIZE;
        }

        return $loaded;
    }
}
