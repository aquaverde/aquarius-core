<?php 

/** Base class for actions to change node properties. */
class action_node extends AdminAction {
    var $props = array("class", "command", "node_id");

    /** Allows all commands for siteadmins  */
    function permit_user($user) {
        if ($this->node_id != 'new') {
            // Check that the node exists
            $node = db_Node::staticGet($this->node_id);
            if (!$node) return false;
        }

        // Only superadmins may change node properties
        if (in_array($this->command, array('editprop', 'editprop_save', 'change_form'))) {
            if($this->command == 'change_form') return true;
            return $user->isSuperadmin();
        }

        // Users may toggle activation of nodes they have edit permission for
        if ($this->command == 'toggle_active') return $user->may_activate($node);
        
        // Normal users may move nodes within boxes if they have edit permission on them. Siteadmins may copy everything except the root node.
        if (in_array($this->command, array('moveorcopy', 'move', 'copy'))) {
            return !$node->is_root() && (($node->is_boxed() && $user->may_edit($node) && $user->copy_permission) || $user->isSiteadmin());
        }

        if (strpos($this->command, 'delete') === 0) {
            return !$node->is_root() && $user->may_delete($node);
        }
        // Fail safely
        return false;
    }

    function load_node($allow_new = false) {
        if (is_numeric($this->node_id)) {
            return db_Node::get_node($this->node_id);
        } elseif ($allow_new && $this->node_id == 'new') {
            $parent = db_Node::get_node($this->params[0]); // Expect parent_id in the first additional parameter
            if (!$parent) throw new Exception("Missing or incorrect parent id '".$this->params[0]."'");
            $node = DB_DataObject::factory('node');
            $node->parent_id = $parent->id;
            $node->active = ADMIN_INIT_NODE_ACTIVE;
            return $node;
        }
        if (!$init) throw new Exception("Could not load node id '$this->node_id'");
        return $node;
    }

    /** Get tree of target parents where a node may be moved within a box.
      * This allows 'horizontal' movements only, as from one category into another. But a content node in a sub-category may be moved into another sub-category (wich may be in another category) as well, for example if you want to move node "Hotels/Dotzigen/Tinu's Schopf" to "Herbergen/Dotzigen/Tinu's Schopf".
      * @param $node get possible parents for this node
      * @param $as_list return list of possible parent IDs instead of tree
      * @return flattened tree where the leafs are the permitted target parents (the parent of $node at least); false if node is not in a box or may not be moved. The nodeinfo array will have 'is_possible_parent' = true for the concerned nodes.
      */
    protected function _possible_parents($node, $as_list=false) {
        // Only boxed nodes may be moved
        if (!$node->is_boxed()) return false;

        // Get the parent box
        $box_node = $node;
        while ($box_node->is_boxed()) {
            $box_node = $box_node->get_parent();
        }

        // Depth of the nodes we're interested in
        $parent_depth = $node->depth() - 1;
        $relative_depth = $parent_depth - $box_node->depth();

        // Only nodes below categories can be moved
        if ($relative_depth < 1) return false;

        // Make filter that selects possible parents
        $possible_parent_filter = new NodeFilter(create_function('$node, $parent_depth', 'return $node->depth() == $parent_depth;'), $parent_depth);
        // Build a tree with box as root, to the depth of parent of node (purging branches that have no nodes at the required depth)
        $tree = NodeTree::build($box_node, false, false, false, $possible_parent_filter, $relative_depth);

        if ($as_list) {
            // Collect all parent node IDs in list
            return NodeTree::walk(
                $tree,
                create_function('$node, $list', '$list[] = $node->id; return $list;'),
                $possible_parent_filter,
                false,
                array()
            );
        } else {
            $flat_tree = NodeTree::flatten($tree);

            // Annotate nodeinfo
            foreach($flat_tree as &$nodeinfo) {
                $nodeinfo['is_possible_parent'] = $possible_parent_filter->pass($nodeinfo['node']);
            }
            
            return $flat_tree;
        }
    }
}


/** Toggle active flag for a node  */
class action_node_toggle_active extends action_node implements ChangeAction {
    function process($aquarius, $post, $result) {
        $node = $this->load_node();
        $node->active = !$node->active;
        $node->update();
        $result->add_message(new Translation("s_message_node_".($node->active?"":"de")."activated", array($node->title)));
        $result->touch_region(new Node_Change_Notice($node, false, true));
        $result->touch_region('db_dataobject');
    }
}


/** Toggle access_restriction flag */
class action_node_toggle_lock extends action_node implements ChangeAction {
    function process($aquarius, $post, $result) {
        $node = $this->load_node();
        $node->access_restricted = !$node->access_restricted;
        $node->update();
        $node->update_cache();

        // delete all restrictions pointing to this node
        if ( !$node->access_restricted ) {
                // delete old restricons
                $restriction = DB_DataObject::factory('fe_restrictions');
                $restriction->node_id = $this->node_id;
                $restriction->delete();
        }
        $result->add_message(new Translation("s_message_node_access_restriction_".($node->active?"":"de")."activated", array($node->title)));
        $result->touch_region(new Node_Change_Notice($node, false, true));
        $result->touch_region('db_dataobject');
    }
}


/** Delete dialog for nodes */
class action_node_delete_dialog extends action_node implements DisplayAction {
    function get_icon()  { return 'trash'; }
    function get_title() { return new Translation('s_delete_content'); }

    function process($aquarius, $request, $smarty, $result) {
        $node = $this->load_node();
        $smarty->assign('node', $node);
        $smarty->assign('children', $node->children());
        $smarty->assign('actions', array(
            Action::make('node', 'delete', $node->id),
            Action::make('cancel')
        ));
        $result->use_template('delete_dialog.tpl');
    }
}


/** Remove node and all its children */
class action_node_delete extends action_node implements ChangeAction {
    function get_title() { return new Translation('s_delete_content'); }
    function process($aquarius, $post, $result) {
        $node = $this->load_node();
        $node->delete();
        $result->touch_region(new Node_Change_Notice($node, true, true));
        $result->touch_region('db_dataobject');
        $result->add_message(AdminMessage::with_line('ok', "s_message_node_deleted", $node->title));
    }
}


/** dialog to run a move or copy operation
  *
  * Normally, nodes may only be moved within boxes. To this end, a list of
  * possible parents is prepared so the user can choose from those. Siteadmins may instead choose to move nodes anywhere they please.
  * */
class action_node_moveorcopy extends action_node implements DisplayAction {
    function get_icon()  { return 'move'; }
    function get_title() { return new Translation('s_move_copy'); }

    function process($aquarius, $request, $smarty, $result) {
        $node = $this->load_node();
        $moveaction = false;

        $may_move_anywhere = db_Users::authenticated()->isSiteAdmin();

        $possible_parents = $this->_possible_parents($node);
        if ($may_move_anywhere || $possible_parents) {
            $moveaction = Action::make('node', 'move', $this->node_id);
        }

        global $lg;
        if ($may_move_anywhere) {
            $smarty->assign("select_from_all", Action::make('nodes_select', 'tree', 0, $lg, 'root', false, '', false, $this->node_id));
        }

        $smarty->assign("node", $node);
        $smarty->assign('parent', $node->get_parent());
        $smarty->assign('possible_parents', $possible_parents);
        $smarty->assign("moveaction", $moveaction);
        $smarty->assign("copyaction", Action::make('node', 'copy', $this->node_id));
        $result->use_template('moveorcopy.tpl');
    }
}


/** Move a node to another parent.
  * Only 'horizontal' movements are allowed. This means that nodes may only be moved inside a box, and there only on the same level.
  * The request variable 'node_target' must be set to a node id. If this variable is not set, no action is taken. */
class action_node_move extends action_node implements ChangeAction {
    function get_title() { return new Translation('s_moveit'); }

    function process($aquarius, $post, $result) {
        $this->move($this->load_node(), $post, $result);
    }

    function move($node, $post, $result) {
        $new_parent_id = get($post, 'node_target');

        // Execute only if we have a target, silently ignore other cases
        $new_parent = false;
        if (is_numeric($new_parent_id)) {
            // Check that parent is valid
            $parent_allowed = false;
            $new_parent = DB_DataObject::factory('node');
            if (!$new_parent->get($new_parent_id)) throw new Exception("Invalid node_target id parameter, node with id '$new_parent_id' does not exist");
        }

        if ($new_parent && $new_parent->id != $node->get_parent()->id) {
            $user = db_Users::authenticated();

            if (!$user->isSiteadmin() && !in_array($new_parent->id, $this->_possible_parents($node, true))) {
                throw new Exception("Parent ".$node->idstr()." not permitted for ".$node->idstr());
            }
            
            $old_parent = $node->get_parent();
            $node->move($new_parent);

            $result->touch_region(Node_Change_Notice::structural_change_to($old_parent));
            $result->touch_region(Node_Change_Notice::structural_change_to($node));

            $result->add_message(AdminMessage::with_line('ok', "s_message_node_moved", $node->get_contenttitle(), $new_parent->get_contenttitle()));
        }
    }
}


/** Copy a node, maybe to another parent.
  * This is an extension to the move action which allows cloning the node instead of moving it. */
class action_node_copy extends action_node_move implements ChangeAction {
    function get_title() { return new Translation('s_copyit'); }

    function process($aquarius, $post, $result) {
        $node = $this->load_node();
        $newnode = $node->copy(" ".str(new Translation('s_move_copy_title')));
        $result->touch_region(Node_Change_Notice::structural_change_to($newnode));
        $result->add_message(AdminMessage::with_line('ok', "s_message_node_cloned", $node->get_contenttitle(), $newnode->get_contenttitle()));
        
        $this->move($newnode, $post, $result);
    }
}

/** Edit properties of the node. node_id may be 'new', in this case an additional parameter for the parent_id is expected
  * example: 'action:node:editprop:new:12' */
class action_node_editprop extends action_node implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        $node = $this->load_node(true);
        
        // List of forms ($id => $form->title), initialized with an empty entry
        $allForms = array("" => "");
        
        // Add all forms
        $prototype = DB_DataObject::factory('form');
        $prototype->orderBy("title");
        foreach (getList($prototype) as $form)
                $allForms[$form->id] = htmlspecialchars($form->title);

        if (!$node->is_root()) {
            $parent_select_action = Action::make('nodes_select', 'tree', 'parent_id', db_Languages::getPrimary(), 'root', false, '', false, $this->node_id);
            $smarty->assign('parent_select_action', $parent_select_action);
        }
        
        $smarty->assign('parent', $node->get_parent());
        $smarty->assign('allForms', $allForms);
        $smarty->assign('submitaction', Action::make('node', 'editprop_save', $this->node_id, get($this->params, 0)));
        $smarty->assign('node', $node);
        $smarty->assign('form', $node->get_form());
        $result->use_template('nodeedit.tpl');
    }
}


/** Save changes to node properties */
class action_node_editprop_save extends action_node implements ChangeAction {
    function process($aquarius, $post, $result) {
        $node = $this->load_node(true);
        $valid = true;
        // Read string values from POST
        $fields = array("name", "form_id", "childform_id", "contentform_id", "box_depth", "weight", "title");
        if (!$node->is_root()) $fields []= "parent_id";
        foreach ($fields as $field) {
            $tmp = get($post, $field);
            if ($tmp === false) {
                $valid = false;
                $result->add_message(new Translation('s_message_invalid_value', array($field)));
            } else {
                $node->$field = $tmp;
            }
        }

        // Special handling for checkbox values
        $node->access_restricted = get($post, 'access_restricted', 0);
        $node->active            = get($post, 'active', 0);

        // Commit
        if ($valid) {
            $node->last_change = time();
            if (is_numeric($this->node_id)) {
                $node->update();
                $result->add_message(new Translation('s_message_node_updated', array($node->title)));
            } else {
                $node->insert();
                $result->add_message(new Translation('s_message_node_inserted', array($node->title)));
            }
            $result->touch_region(Node_Change_Notice::structural_change_to($node));

        } else {
            // Edit again if there are invalid fields
            $result->inject_action(Action::make('node', 'editprop', $this->node_id));
        }
    }
}


/** Change the main form of a node
  * The form id is taken from POST value 'form_id', special string 'null' may be given to select no form.
  */
class action_node_change_form extends action_node implements ChangeAction {
    function process($aquarius, $post, $result) {
        $node = $this->load_node();
        $valid = false;
        $form_id = requestvar('form_id');
        if ($form_id) {
            if ($form_id == 'null') {
                $node->form_id = 0;
                $valid = true;
            } else {
                // Validate form_id
                $form = db_DataObject::staticGet('db_Form', $form_id);
                if ($form) {
                    $node->form_id = $form->id;
                    $valid = true;
                }
            }
        }
        if ($valid) {
            $node->update();
            $result->touch_region(Node_Change_Notice::affecting_children_of($node));
            $result->add_message(new Translation('s_message_form_changed'));
        } else {
            throw new Exception("Received invalid form id ($form_id) for node ($node->id)");
        }
    }
}

