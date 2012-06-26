<?php
/**
 * Table Definition for fieldgroup_selection
 */
require_once 'DB/DataObject.php';

class db_Fieldgroup_selection extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'fieldgroup_selection';            // table name
    public $fieldgroup_selection_id;         // int(11)  not_null primary_key auto_increment
    public $name;                            // string(765)  not_null
    public $is_standard;                     // int(1)  not_null

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Fieldgroup_selection',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    static function all() {
        $proto = new self();
        return getList($proto);
    }

    static function standard() {
        $g = new self();
        $g->is_standard = 1;
        $found = $g->find(true);
        if (!$found) {
            $g = new self();
            $found = $g->find(true);
        }
        if ($found) return $g;
        return false;
    }

    function selected_groups() {
        global $DB;
        $groups = $DB->listquery("
            SELECT fieldgroup_id
            FROM fieldgroup_selection_entry
            JOIN fieldgroup_selection USING (fieldgroup_selection_id)
            WHERE fieldgroup_selection.fieldgroup_selection_id = ".intval($this->fieldgroup_selection_id)
        );
        $selected_groups = array();
        foreach($groups as $group_id) {
            $group = DB_DataObject::factory('fieldgroup');
            $group->get($group_id);
            $selected_groups[$group_id] = $group;
        }

        // Sort groups by weight
        require_once "lib/Compare.php";
        uasort($selected_groups, ObjectCompare::by_field('weight', 'intcmp'));

        return $selected_groups;
    }

    /** Add field group to selection
      * @param $group_id
      * @return Dataobject for the added group
      * If the group is already selected, no action is taken. */
    function add_group($group_id) {
        $group_proto = DB_DataObject::factory('fieldgroup');
        $found = $group_proto->get($group_id);
        if (!$found) throw new Exception("Invalid fieldgroup_id '$id' in new group selection");

        $add_proto = DB_DataObject::factory('fieldgroup_selection_entry');
        $add_proto->fieldgroup_selection_id = $this->fieldgroup_selection_id;
        $add_proto->fieldgroup_id = $group_proto->fieldgroup_id;
        $found = $add_proto->find();
        if (!$found) {
            $success = $add_proto->insert();
            if (!$success) throw new Exception("Failed to insert fieldgroup_selection_entry for group '$add_proto->fieldgroup_id' selection '$add_proto->fieldgroup_selection_id'");
        }
        return $group_proto;
    }

    /** Remove field group from selection
      * @param $group_id
      * If the group is not selected, no action is taken */
    function del_group($group_id) {
        $remove_proto = DB_DataObject::factory('fieldgroup_selection_entry');
        $remove_proto->fieldgroup_selection_id = $this->fieldgroup_selection_id;
        $remove_proto->fieldgroup_id = $group_id;
        $found = $remove_proto->find();
        if ($found) {
            $success = $remove_proto->delete();
            if (!$success) throw new Exception("Failed to delete fieldgroup_selection_entry between group '$remove_proto->fieldgroup_id' and selection '$remove_proto->fieldgroup_selection_id'");
        }
    }

    function delete() {
        foreach($this->selected_groups() as $group) {
            $this->del_group($group->fieldgroup_id);
        }
        parent::delete();
    }

}
