<?php 

class action_fegroup extends AdminAction {
    var $props = array("class", "command", "id");

    function permit_user($user) {
      return $user->isSiteadmin();
    }
}

class action_fegroup_list extends action_fegroup implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $fe_groups = DB_DataObject::factory('fe_groups');
        $fe_groups->find();

        $groups = array();

        while ($fe_groups->fetch()) {
            $groups[] = clone($fe_groups);
        }

        $smarty->assign("groups", $groups);
 
        $result->use_template('fe_group_list.tpl');
    }
}


class action_fegroup_edit extends action_fegroup implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $fe_groups = DB_DataObject::factory('fe_groups');

        $fe_groups->id = $this->id;
        $fe_groups->find(true);
        
        // load the node tree
        $rootnode = db_Node::get_root();
        $purge_filter = NodeFilter::create('access_restricted', true);
        $nodelist = NodeTree::build_flat($rootnode, array(), false, false, $purge_filter);
            
        // get the stored restriction for this group
        $restr_proto = DB_DataObject::factory('fe_restrictions');
        $restr_proto->group_id = $this->id;
        $restr_proto->find();
        
        $restrictions = array();
        
        while ( $restr_proto->fetch() )
            $restrictions[$restr_proto->node_id] = true;
        
        $smarty->assign("restrictions", $restrictions);
        $smarty->assign("nodelist", $nodelist);
        $smarty->assign("group", $fe_groups);
        $result->use_template('fe_group_edit.tpl');

    }
}


class action_fegroup_save extends action_fegroup implements ChangeAction {
    function process($aquarius, $post, $result) {
        $fe_groups = DB_DataObject::factory('fe_groups');
        
        // set the new group name
        $fe_groups->name = get($post, 'groupName');

        // save the group
        if ( $this->id != "null" ) {
            $fe_groups->id = $this->id;
            $fe_groups->update();
        } else {
            $fe_groups->insert();
        }
        
        // delete old restricons
        $restriction = DB_DataObject::factory('fe_restrictions');
        $restriction->group_id = $fe_groups->id;
        $restriction->delete();

        if ( !empty($_POST['nodeId']) ) {
            // set the new restrictions and save them
            foreach ( array_keys(get($post, 'nodeId', array())) as $key ) {
                $restriction->node_id = $key;
                $restriction->insert();
            }
        }
    }
}


class action_fegroup_delete extends action_fegroup implements ChangeAction {
    function process($aquarius, $post, $result) {
        $fe_groups = DB_DataObject::factory('fe_groups');

        $fe_groups->get($this->id);
        $fe_groups->delete(); 
        $result->add_message("Group deleted");
    }
}


class action_fegroup_toggle_active extends action_fegroup implements ChangeAction {
    function process($aquarius, $post, $result) { 
        $fe_groups = DB_DataObject::factory('fe_groups');                  $fe_groups->get($this->id);
        $fe_groups->active = !$fe_groups->active;
        $fe_groups->update();
        $result->add_message("Group switched");
    }
}
