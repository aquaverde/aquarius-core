<?php

class action_fieldgroup extends AdminAction {
    
    var $props = array("class", "command");
    
    /** permit for superuser */
    function permit_user($user) {
        return $user->isSuperadmin();
    }

    /** Using the object's ID property, load object from given table */
    function load($table, $allow_new = false) {
        $thing = db_DataObject::factory($table);
        if ($allow_new && $this->id == 'new') {
            return $thing;
        } else {
            $found = $thing->get($this->id);
            if (!$found) throw new Exception("Invalid $table id '$this->id'");
        }
        return $thing;
    }
}


class action_fieldgroup_overview extends action_fieldgroup implements DisplayAction {
    function get_title() { return new FixedTranslation('Field Groups'); }
    function process($aquarius, $request, $smarty, $result) {
        $selection = db_DataObject::factory('fieldgroup_selection');
        $selection->orderBy('name');
        $selection->find();

        $fieldgroup = db_DataObject::factory('fieldgroup');
        $fieldgroup->orderBy('weight');
        $fieldgroup->find();

        $smarty->assign('selection', $selection);
        $smarty->assign('fieldgroup', $fieldgroup);
        $smarty->assign('new_selection', Action::make('fieldgroup', 'edit_selection', 'new'));
        $smarty->assign('new_fieldgroup', Action::make('fieldgroup', 'edit_group', 'new'));
        $result->use_template("fieldgroup_overview.tpl");
    }
}


/** Just a bit of inheritance hell to use common get_title and get_icon methods. (Refactor topic, these...) */
class action_fieldgroup_edit extends action_fieldgroup {
    var $props = array("class", "command", "id");
    var $topic = "";
    function get_title() { 
        return ($this->id=='new'? "New" : "Edit")." $this->topic";
    }
    function get_icon() {
        return $this->id=='new'?'new'
                               :'pencil';
    }
}


class action_fieldgroup_edit_selection extends action_fieldgroup_edit implements ChangeAndDisplayAction {
    var $topic = "selection";

    function process($aquarius, $post, $smarty, $change_result, $display_result) {
        $selection = $this->load('fieldgroup_selection', true);

        $make_standard = isset($post['make_standard_'.$this->id]);
        $save_selection = $make_standard || isset($post['save_selection_'.$this->id]);
        if ($save_selection) {
            if ($make_standard) {
                global $DB;
                $DB->query("UPDATE fieldgroup_selection SET is_standard = 0");
                $selection->is_standard = 1;
                $change_result->add_message(new FixedTranslation("Saved '$selection->name' as standard selection"));
            }
            $selection->name = get($post, 'name');
            $this->id == 'new' ? $selection->insert() : $selection->update();
            $change_result->add_message(new FixedTranslation("Saved changes to selection '$selection->name'"));
        
            $new_group_ids = get($post, 'selected', array());
            $old_group_ids =  array_keys($selection->selected_groups());

            $add_groups = array_diff($new_group_ids, $old_group_ids);
            $remove_groups = array_diff($old_group_ids, $new_group_ids);
            if (!empty($add_groups)) {
                $added_groups = array();
                foreach($add_groups as $add_id) {
                    $added_group = $selection->add_group($add_id);
                    $added_groups []= $added_group->name;
                }
                $change_result->add_message(new FixedTranslation("Added groups: ".join(',', $added_groups)));
            }
            if (!empty($remove_groups)) {
                $removed_groups = array();
                foreach($remove_groups as $remove_id) {
                    $selection->del_group($remove_id);
                    $group_proto = DB_DataObject::factory('fieldgroup');
                    $group_proto->get($remove_id);
                    $removed_groups []= $group_proto->name;
                }
                $change_result->add_message(new FixedTranslation("Removed groups: ".join(',', $removed_groups)));
            }
        } else {
            $smarty->assign('selection', $selection);
            $smarty->assign('selected_groups', array_keys($selection->selected_groups()));
            $group = db_DataObject::factory('fieldgroup');
            $smarty->assign('groups', $group->all());
            $display_result->use_template('fieldgroup_edit_selection.tpl');
        }
    }
}


class action_fieldgroup_edit_group extends action_fieldgroup_edit implements ChangeAndDisplayAction {
    var $topic = "group";

    function process($aquarius, $post, $smarty, $change_result, $display_result) {
        $group = $this->load('fieldgroup', true);
        $new = $this->id == 'new';
        if ($new) {
            $group->visibility_level = 2;
            $group->weight = 10000;
        }

        if(isset($post['save_group_'.$this->id])) {
            $group->name = get($post, 'name');
            $group->display_name = get($post, 'display_name');
            $group->visibility_level = intval(get($post, 'visibility_level'));
            $new ? $group->insert() : $group->update();
            $group->clean_weight();
            $selectors = array_filter(get($post, 'selectors'));
            natsort($selectors);
            $group->update_selectors(array_reverse($selectors));
            $change_result->add_message("Saved group $group->name (".count($selectors)." fields)");
        } else {
            $smarty->assign('group', $group);
            $smarty->assign('selectors', array_merge($group->field_selectors(), array_fill(0, 10, '')));
            $smarty->assign('visibility_levels', db_Users::$status_names);
            $display_result->use_template('fieldgroup_edit_group.tpl');
        }
    }
}


class action_fieldgroup_delete_group extends action_fieldgroup implements ChangeAction {
    var $props = array("class", "command", "id");
    function get_title() { return "Delete Group"; }
    function get_icon() { return 'trash'; }
    function process($aquarius, $post, $result) {
        $group = $this->load('fieldgroup');
        $group->delete();
        $result->add_message(new FixedTranslation("Fieldgroup $group->name deleted"));
    }
}


class action_fieldgroup_delete_selection extends action_fieldgroup implements ChangeAction {
    var $props = array("class", "command", "id");
    function get_title() { return "Delete Selection"; }
    function get_icon() { return 'trash'; }
    function process($aquarius, $post, $result) {
        $selection = $this->load('fieldgroup_selection');
        if (!$selection) throw new Exception("Invalid fieldgroup selection id '$this->id'");
        $selection->delete();
        $result->add_message(new FixedTranslation("Fieldgroup $selection->name deleted"));
    }
}


class action_fieldgroup_move_group extends action_fieldgroup implements ChangeAction {
    var $props = array("class", "command", "id", "direction");
    function get_title() { return "Move group $this->direction"; }
    function get_icon() { return "chevron-".$this->direction; }
    function process($aquarius, $post, $result) {
        $group = $this->load('fieldgroup');
        $group->weight += 15 * ($this->direction == "up" ? -1 : 1);
        $group->update();
        $group->clean_weight();
        $result->add_message("Moved group $this->direction");
    }
}

