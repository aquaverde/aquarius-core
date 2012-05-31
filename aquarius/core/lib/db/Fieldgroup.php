<?php
/** Fieldgroups are used to gather form fields into a group for display.
  * A fieldgroup names form fields by their name or selects them with a glob. For example, a 'pictures' group might list the fields 'picture_header', 'picture1', 'picture2', 'pictures'; or simply use the glob 'picture*' to select all fields starting with the string 'picture'.
  *
  * Fieldgroups have two names: a basic name and a display name. The basic name helps keeping apart similar groups. For example, you could have a group 'main' and a group 'main with downloads', where 'main' selects the fields 'title', and 'text', whereas 'main with downloads' selects the fields 'title', 'text', and 'download*'. The display name is used as display to the user, in this example, it is likely that both groups have the display name 'main'.
  *
  * Each group has a visibility level specifying the required user permission level for the group to be displayed. Note that this is not supposed to be secure, it's just convenient to hide things users do not need.
  */
require_once 'DB/DataObject.php';

class db_Fieldgroup extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fieldgroup';                      // table name
    public $fieldgroup_id;                   // int(11)  not_null primary_key auto_increment
    public $name;                            // string(765)  not_null
    public $display_name;                    // string(765)  not_null
    public $visibility_level;                // int(6)  not_null multiple_key
    public $weight;                          // int(11)  not_null multiple_key

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Fieldgroup',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    static function all() {
        $proto = new self();
        $proto->orderBy('weight');
        return getList($proto);
    }

    /** Title for this group
        * The title of a group is either the translation of display_name, else the raw display_name. The idea being that there will be translations for common titles, but it should still be possible to choose project specific titles. */
    function title() {
        return Translation::for_key($this->display_name, $this->display_name);
    }

    /** List of field selectors */
    function field_selectors() {
        if (!$this->fieldgroup_id) {
            return array();
        } else {
            static $cached = array();
            if (!isset($cached[$this->fieldgroup_id])) {
                $field = DB_DataObject::factory('fieldgroup_entry');
                $field->fieldgroup_id = $this->fieldgroup_id;
                $field->find();
                $cached[$this->fieldgroup_id] = getList($field);
            }
            return $cached[$this->fieldgroup_id];
        }
    }

    /** Check whether a given field name is selected by the group
      * @param $field_name string checked for a match against selectors
      * @return boolean whether field is selected */
    function selects($field_name) {
        foreach($this->field_selectors() as $selector) {
            if (fnmatch($selector->selector, $field_name)) return true;
        }
        return false;
    }

    /** Overwrite the list of field selectors in this group with a new list */
    function update_selectors($selectors) {
        $entry = DB_DataObject::factory('fieldgroup_entry');
        if ($this->fieldgroup_id) {
            $entry->fieldgroup_id = $this->fieldgroup_id;
            $entry->delete();
        }

        foreach($selectors as $selector) {
            $entry->selector = $selector;
            $success = $entry->insert();
            if (!$success) throw new Exception("Unable to insert selector '$selector' for group $this->fieldgroup_id");
        }
    }

    function delete() {
        foreach($this->field_selectors() as $selector) {
            $selector->delete();
        }
        parent::delete();
    }

    function clean_weight() {
        $index = 1;
        foreach(self::all() as $group) {
            $group->weight = 10*$index++;
            $group->update();
        }
    }
}
