<?php
/** @package Aquarius */

/**
 * Language specific content for nodes
 *
 * Content is attached to a node (node_id) and has a language (lg).
 */
class db_Content extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'content';                         // table name
    public $node_id;                         // int(11)  not_null multiple_key group_by
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $lg;                              // char(6)  
    public $cache_title;                     // varchar(750)  multiple_key
    public $cache_fields;                    // blob(196605)  blob
    public $active;                          // tinyint(1)  not_null multiple_key group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Content',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /** Set default values */
    function __construct() {
        
    }
    
    /** Load a content object, storing the object in a global cache for future use */
    static function get_cached($node_id, $lg = false) {
        if (!$lg) $lg = $GLOBALS['lg'];
        $lg = str($lg);
        if (!@isset($GLOBALS['_AQUARIUS_CONTENT_CACHE'][$node_id][$lg])) {
            $content = DB_DataObject::factory('content');
            $content->lg = $lg;
            $content->node_id = $node_id;
            $found = $content->find();
            if ($found > 1) throw new Exception("Found $found entries for content $node_id/$lg"); // The database is supposed to enforce unique $lg/$node_id combinations
            if ($found > 0) {
                $content->fetch();
                $GLOBALS['_AQUARIUS_CONTENT_CACHE'][$node_id][$lg] = $content;
            } else {
                $GLOBALS['_AQUARIUS_CONTENT_CACHE'][$node_id][$lg] = false;
            }
        }
        return $GLOBALS['_AQUARIUS_CONTENT_CACHE'][$node_id][$lg];
    }

    /** Get the node of this content.
      * All content must be associated to a node, this function always returns a node (or throws an exception) */
    function get_node() {
        $node = DB_DataObject::staticGet('db_node', $this->node_id); // staticGet caches
        if (!$node) throw new Exception("No node with id '$this->node_id' for content $this->id.");
        return $node;
    }


    /** Initialize content fields with empty array or null; according to form */
    function initialize_properties($formfields) {
         foreach($formfields as $formfield) {
             $fieldname = $formfield->name;
             if (strlen($fieldname)) $this->$fieldname = $formfield->multi? array() : null;
         }
    }


    /** Load the content field values into object properties.
      * The fields are not reloaded on successive calls to this method.
      * @returns true if the fields were (already) loaded, false otherwise.
      */
    function load_fields() {
        if (isset($this->_loaded_fields)) return true;

        $loaded = $this->load_cache();
        if (!$loaded) {
            $loaded = $this->load_db();
        }
        $this->_loaded_fields = true;
        return false;
    }


    /** Load content fields from cache
      * @return true if the fields could be loaded */
    function load_cache() {
        if (0 === strlen($this->cache_fields)) return false;

        $cache_val = unserialize($this->cache_fields);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::debug("Unable to load cache of content id $this->id, error ".json_last_error());
            return false;
        }
        foreach($cache_val as $key => $value) {
            $this->$key = $value;
        }
        return true;
    }


    function load_db() {
        if ($this->id == null) return false;
        global $aquarius;
    
        // Get all content fields for this content (including language_independent fields)
        $fieldvals = $aquarius->db->query('
            SELECT
                cf.id,
                cf.name,
                cfv.name,
                cfv.value
            FROM content c
            JOIN content_field cf ON c.id = cf.content_id
            JOIN content_field_value cfv ON cf.id = cfv.content_field_id
            WHERE c.id = ?
            ORDER BY cf.weight
        ', array($this->id));

        $content_field_values = array();
        while($field = $fieldvals->fetchRow()) {
            list($field_id, $field_name, $value_name, $value) = $field;

            if ($value_name) {
                $content_field_values[$field_name][$field_id][$value_name] = $value;
            } else {
                $content_field_values[$field_name][$field_id][] = $value;
            }
        }

        $formfields = $this->get_formfields();
        $this->initialize_properties($formfields);

        $formtypes = $aquarius->get_formtypes();
        $cache = array();
        foreach($formfields as $field_name => $form_field) {
            $formtype = $formtypes->get_formtype($form_field->type);
            if (!is_object($formtype)) throw new Exception("Formtype '$form_field->type' does not exist");
            
            $field_values = get($content_field_values, $field_name, array());
            $value = $formtype->db_get_field($field_values, $form_field, $this->lg);
            $this->$field_name = $value;
            $cache[$field_name] =  $value;
        }
        $cache_val = serialize($cache);

        $this->cache_fields = $cache_val;
        $success = parent::update();
        if (!$success) Log::warn("Unable to write cache to DB for $this->id");
        
        return true;
    }


    /** Load the fields from the DB on the first access
      *
      * Accessing unset properties of the object will return null without warning.
      */
    function __get($field) {
        $this->load_fields();
        if (isset($this->$field)) return $this->$field;
        return null;
    }

    /** Tries to load language independent fields from another language
      * Useful to initialize new content
      * @return boolean whether another content could be loaded
      */
    function load_language_independent() {
        // Find content of another language, any language. We assume that the language independent fields are synced across languages so we do not care which language it will be.
        // This is what you get if you don't pay attention when developing your system.
        $other_content = DB_DataObject::factory('content');
        $other_content->node_id = $this->node_id;
        $found = $other_content->find(true);

        if ($found) {
            $other_content->load_fields();
            foreach($this->get_formfields() as $formfield) {
                if ($formfield->language_independent) {
                    $this->{$formfield->name} = $other_content->{$formfield->name};
                }
            }
        }
        return $found;
    }
    
    /** Write content fields from properties to DB */
    function save_content() {
        global $aquarius;
        if (!is_numeric($this->id)) throw new Exception("Trying to save_content() on non persistent content (invalid id '$this->id')");

        // Make list of content in all languages
        $content_proto = DB_DataObject::factory('content');
        $content_proto->node_id = $this->node_id;
        $content_proto->find();
        $content_langs = array();
        while($content_proto->fetch()) $content_langs[] = clone $content_proto;
        
        // Just delete all content fields and write them anew
        // To make this more robust, we might first select the content_field_ids for this content and delete them only after the new ones have been inserted, this would prevent lost content on insert failures.
        $this->delete_contentfields();
        
        $formtypes = $aquarius->get_formtypes();
        $formfields = $this->get_formfields();
        foreach($formfields as $formfield) {
            $name = $formfield->name;

            $save_to_contents = array($this);

            // In case of multilingual fields we must delete the field in all languages, and save to all of them
            if ($formfield->language_independent) {
                self::delete_contentfield($this->node_id, $name);
                $save_to_contents = $content_langs;
            }

            // Read the value to be written from the content property
            // Check with isset to avoid triggering __get() which might call load_fields() which would overwrite things
            // Shows how bad such hacks are
            $val = null;
            if (isset($this->$name)) {
                $val = $this->$name;
            }
            
            // Let the formtype process the value before writing
            $formtype = $formtypes->get_formtype($formfield->type);
            $val = $formtype->db_set_field($val, $formfield, $this->lg);

            // Write the fields to the DB
            $weight = 0;
            foreach($val as $fieldvals) {
                // Remove empty fields
                if (!is_array($fieldvals)) continue;
                $fieldvals = array_filter($fieldvals, 'strlen');
                
                if (count($fieldvals) > 0) {
                    foreach($save_to_contents as $content) {
                        db_Content_field::write($content->id, $formfield->name, $weight, $fieldvals);
                    }
                }
                $weight += 1;
            }
        }
        $this->cache_fields = ''; // Reset the cache
        $this->cache_title = join(', ', $this->titlefields());

        $this->update();
        
        // Lose the cached object
        unset($GLOBALS['_AQUARIUS_CONTENT_CACHE'][$this->node_id][$this->lg]);
    }


    /** Get values that are supposed to be used as its title for this content
      * Contents of the following fields are returned in this order:
      * - field 'title' if it exists
      * - fields having the 'add_to_title' flag (in the order they are defined in the form)
      * - the first field in the form if there are no other fields used as title
      * The values are converted to string, empty fields are not included in returned list.
      * @return list of titles as strings
      */
    function titlefields() {
        $formfields = $this->get_formfields();
        
        $titlefields = array();
        if (isset($formfields['title'])) {
            $titlefields []= $formfields['title'];
        }
        
        foreach($formfields as $formfield) {
            if ($formfield->add_to_title) {
                $titlefields []= $formfield;
            }
        }

        if (count($titlefields) == 0 && count($formfields > 0)) {
            // we're desperate
            $titlefields []= first($formfields);
        }
        
        $titles = array();
        foreach($titlefields as $field) {
            if (isset($this->{$field->name})) {
                $title = $this->{$field->name};
                $formtype = $field->get_formtype();
                $titlestr = $formtype->to_string($title);
                if (strlen($titlestr) > 0) {
                    $titles []= $titlestr;
                }
            }
        }
        return $titles;
    }


    function fetch() {
        // Reset the loaded_fields flag
        unset($this->_loaded_fields);

        return parent::fetch();
    }
    
    /** Saves content fields as well */
    function insert() {
      $this->active = ADMIN_INIT_CONTENT_ACTIVE;
      parent::insert();
      $this->save_content();
    }

    /** Remove all attached content fields */
    private function delete_contentfields() {
        global $aquarius;
        $aquarius->db->query('
            DELETE cf, cfv
            FROM content_field cf 
            LEFT JOIN content_field_value cfv ON cf.id = cfv.content_field_id
            WHERE cf.content_id = ?
        ', array($this->id));
    }
    
    /** Remove a content field for all languages
      * @param node_id id of the node to delete content field from
      * @param field_name the field to remove */
    static function delete_contentfield($node_id, $field_name=null) {
        global $aquarius;
        $aquarius->db->query( '
            DELETE cf, cfv
            FROM content c
                JOIN content_field cf ON c.id = cf.content_id
                LEFT JOIN content_field_value cfv ON cf.id = cfv.content_field_id
            WHERE c.node_id = ?
                  AND cf.name = ?
        ', array($node_id, $field_name));
    }
    
    /** Delete this content from the DB */
    function delete() {
        $this->delete_contentfields();

        // Call the overridden method to delete $this
        parent::delete();
        
        // Remove from cache
        unset($GLOBALS['_AQUARIUS_CONTENT_CACHE'][$this->node_id][$this->lg]);
    }
    
    /** Get list of fields of the form for this content */
    function get_formfields() {
        $form = $this->get_node()->get_form();
        if ($form) {
            return $form->get_fields();
        }
        return array();
    }

    /** Get the content field values */
    function get_fields() {
        $this->load_fields();
        $fields = array();
        foreach($this->get_formfields() as $form_field) {
            $fields[$form_field->name] = $this->{$form_field->name};
        }
        return $fields;
    }

    /** Return the value of the first field in the form */
    function get_title() {
        return $this->cache_title;
    }

    /** Whether this content and its node are active */
    function active() {
        return $this->active && $this->get_node()->active(true);
    }

    
    /** Answer messages to $this->something() with $this->something.
      * Convenience is king.
      * Example: $content->text() => $content->text;
      */
    function __call($name, $params) {
        $this->load_fields();
        if (isset($this->$name)) return $this->$name;
        return null;
    }
}
