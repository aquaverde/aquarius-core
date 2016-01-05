<?php 

/** The Nodetree actions are used to display trees of nodes.
  * An action to modify a list of nodes is also provided (nodetree_forall).
  * While we try to hide nodes from users that do not have permission to edit them, the tree may still display nodes the users do not have edit permissions for. If they may edit a child of a node, for example.
  *
  * A logged-in user could use custom HTTP requests to discover the nodetree (nodetree_children) for nodes he/she should not see. We don't consider this a security problem, the backend was not designed to keep information secret from its users. We try, however, to ensure that the users cannot change things they are not allowed to.
  */
class action_nodetree extends AdminAction {

    var $props = array('class', 'op');

    function permit_user($user) {
        if (isset($this->section) && $this->section == 'super') return $user->isSuperadmin();
        return true;
    }
}

/** nodetree:show a tree of nodes starting from root
 * 
  * Params:
  *   lg: language to use in titles and edit actions
  *   section: layout and what type of actions to add, one of 'none', 'sitemap', 'contentedit' or 'super' (see NodeTree.php type aprameter)
  */
class action_nodetree_show extends action_nodetree implements DisplayAction {

    var $props = array('class', 'command', 'lg', 'section');

    function process($aquarius, $request, $smarty, $result) {
        $root = db_Node::get_root();
        $open_nodes = NodeTree::get_open_nodes($this->section);
        array_unshift($open_nodes, $root->id); // Always open root node
        $tree = NodeTree::editable_tree($root, $this->lg, $open_nodes);
        NodeTree::add_controls($tree, $open_nodes, $this->section, true, $this->lg);
        $tree['show_toggle'] = false; // Hack: do not show toggle for root node

        $smarty->assign('entry', $tree);
        $smarty->assign('forallaction', Action::make('nodetree', 'forall'));

        $result->use_template("nodetree.tpl");
    }
}

class action_nodetree_children extends action_nodetree implements DisplayAction {

    var $props = array('class', 'command', 'lg', 'section');

    function process($aquarius, $request, $smarty, $result) {
        $node_id = requestvar('node');
        $open = requestvar('open');
        $node = db_Node::get_node($node_id);
        if (!$node) throw new Exception("Unable to load node '".$node_id."'");

        $open_nodes = NodeTree::get_open_nodes($this->section);

        // Add or remove the node to/from the list of open nodes in this section
        if ($open) {
            array_unshift($open_nodes, $node->id);
        } else {
            $open_nodes = array_diff($open_nodes, array($node->id));
        }
        NodeTree::set_open_nodes($this->section, $open_nodes);
        
        $tree = NodeTree::editable_tree($node, $this->lg, $open_nodes);
        NodeTree::add_controls($tree, $open_nodes, $this->section, true, $this->lg);
        
        $smarty->assign('entry', $tree);

        $result->skip_return();
        $result->use_template("nodetree_container.tpl");
    }
}

class action_nodetree_navig_children extends action_nodetree implements DisplayAction {

    var $props = array('class', 'command', 'lg');

    function process($aquarius, $request, $smarty, $result) {

        $node_id = requestvar('node');
        $node = DB_DataObject::staticGet('db_Node', $node_id);
        if (!$node) throw new Exception("Invalid node id: '$node_id'");

        $open_nodes = NodeTree::get_open_nodes('navig');

        if (isset($request['open'])) {
            // Add or remove the node to/from the list of open nodes in this section
            $open = $request['open'];
            if ($open) {
                array_unshift($open_nodes, $node->id);
            } else {
                $open_nodes = array_diff($open_nodes, array($node->id));
            }

            NodeTree::set_open_nodes('navig', $open_nodes);
        }

        $tree = NodeTree::editable_tree($node, $this->lg, $open_nodes);
        NodeTree::add_controls($tree, $open_nodes, 'none', false, $this->lg);

        $smarty->assign('tree', $tree);
        $smarty->assign('node', $node);
        $smarty->assign('lg', $this->lg);

        // Template expects $adminurl with path 'admin.php'.
        // The trouble is that pending actions are added after execution of this action, so we can't clone the url and just use that as adminurl. What do we do? Simple and ugly: we change the path of the global url var and us that as adminurl as well. (Sorry if your head exploded.)
        global $url;
        $url->path = 'admin.php';
        $smarty->assign('adminurl',$url);

        $result->skip_return();
        $result->use_template('navig.nodetree_container.tpl');
    }
}

class action_nodetree_forall extends action_nodetree implements ChangeAndDisplayAction {
    /** List of commands the forall action supports */
    var $commands = array(
        'activate' => 's_activate',
        'deactivate' => 's_deactivate',
        'delete' => 's_delete',
        'move' => 's_moveit'
    );

    /** Removes commands if a user does not have permission for them.
      *
      * This method is not actually supposed to change the object. A lot of
      * things should be done differently for this action.
      */
    function permit_user($user) {
        if (!$user->isSiteadmin()) {
            unset($this->commands['move']);
            if (!$user->delete_permission) {
                unset($this->commands['delete']);
            }
            if (!$user->activation_permission) {
                unset($this->commands['activate']);
                unset($this->commands['deactivate']);
            }
        }
        return parent::permit_user($user);
    }

    function commands() {
        return $this->commands;
    }

    function process($aquarius, $post, $smarty, $change_result, $display_result) {
        $user = db_Users::authenticated();
        $command = requestvar("command");
        if (!array_key_exists($command, $this->commands())) throw new Exception("Command '$command' not available for this user");
        switch($command) {
        case "deactivate":
        case "activate":
            $active = ($command=='activate'); 
            $selected = get($post, 'selected');
            if(is_array($selected) && !empty($selected)) {
                foreach(array_keys($selected) as $node_id) {
                    $node = db_Node::get_node($node_id);
                    if (!$node) throw new Exception("Cannot load selected node id '$node_id'");
                    $node->active = $active;
                    $node->update();
                    $node->update_cache();
                    $change_result->touch_region(Node_Change_Notice::affecting_children_of($node));
                }
                $change_result->add_message(new Translation("s_message_nodes_{$command}d"));
                $change_result->touch_region('content');
            } else {
                $change_result->add_message(new Translation("s_message_no_selected_nodes"));
            }
            break;
        case "delete":
            $nodenames = array();
            $selected = array_keys(get($post, 'selected', array()));
            if (count($selected) > 0) {
                $nodelist = array();
                foreach($selected as $node_id) {
                    $node = or_die(db_Node::get_node($node_id), "Unable to find node for id %s", $node_id);
                    $nodelist[$node_id] = $node->get_contenttitle()." ($node_id)";
                }
                $delete_action = Action::make('nodetree', 'nodes_delete');
                $smarty->assign('title', $delete_action->get_title());
                $smarty->assign('text_top', new Translation("s_delete_confirm_list"));
                $smarty->assign('list', $nodelist);
                $smarty->assign('actions', array($delete_action, Action::make('cancel')));
                $display_result->use_template('confirm_list.tpl');
            } else {
                $change_result->add_message(new Translation("s_message_no_selected_nodes"));
            }
            break;
        case "move":
            $nodenames = array();
            $selected = array_keys(get($post, 'selected', array()));
            if (count($selected) > 0) {
                $nodelist = array();
                $common_ancestor = false;
                foreach($selected as $node_id) {
                    $node = or_die(db_Node::get_node($node_id), "Unable to find node for id %s", $node_id);
                    if ($node->is_root()) continue; // Just ignore root node, we would have to make the new target the root node which would rarely be desirable (and complicated too)
                    if (!$common_ancestor) {
                        $common_ancestor = $node->get_parent();
                    } else {
                        while(
                            !$common_ancestor->is_root() 
                         && $common_ancestor->id !== $node->id 
                         && !$common_ancestor->ancestor_of($node)
                        ) {
                            $common_ancestor = $common_ancestor->get_parent();
                        }
                    }
                    $nodelist[$node_id] = $node->idstr();
                }
                
                $selected_ids = array_keys($nodelist);
                
                global $lg;
                $smarty->assign("parent_selection", Action::make('nodes_select', 'tree', 0, $lg, 'root', false, '', false, join(',', $selected_ids)));
                $move_action = Action::make('nodetree', 'nodes_move', join(',', $selected_ids));
                $smarty->assign('common_ancestor', $common_ancestor);
                $smarty->assign('list', $nodelist);
                $smarty->assign('actions', array($move_action, Action::make('cancel')));
                $display_result->use_template('nodetree_nodes_move.tpl');
            } else {
                $change_result->add_message(new Translation("s_message_no_selected_nodes"));
            }
            break;
        default:
            throw new Exception("Command '$command' not valid");
        }
    }
}

class action_nodetree_nodes_delete extends action_nodetree implements ChangeAction {

    function get_title() {
        return new Translation('s_delete_nodes');
    }

    function process($aquarius, $post, $change_result) {
        $user = db_Users::authenticated();
        $selected = get($post, 'list');
        $selected = array_reverse($selected); // Start from last node, so that we (hopefully) don't delete a parent and try to delete a child afterwards
        foreach($selected as $node_id) {
            $node = or_die(db_Node::get_node($node_id), "Unable to find node for id %s", $node_id);
            $change_result->touch_region(Node_Change_Notice::structural_change_to($node->get_parent()));

            if (!$user->may_delete($node)) throw new Exception('User '.$user->idstr().' may not delete '.$node->idstr());
            $node->delete();
            $nodenames[] = $node->get_contenttitle();
        }
        $change_result->add_message(new Translation("s_message_node_deleted", array(join(",", $nodenames))));
        $change_result->touch_region('content');
    }
}

/** Move a bunch of nodes to a new parent
  * List of node ids to move is in request parameter 'list'. New parent is in
  * request parameter 'node_target'.
  */
class action_nodetree_nodes_move extends action_nodetree implements ChangeAction {
    
    function permit_user($user) {
        return $user->isSiteadmin();
    }

    function get_title() {
        return new Translation('s_moveit');
    }

    function process($aquarius, $post, $change_result) {
        $selected = get($post, 'list');
        
        $target_nodestr = get($post, 'node_target');
        $node_target = or_die(db_Node::get_node($target_nodestr), "Unable to find target node for id %s", $target_nodestr);

        foreach($selected as $node_id) {
            $node = or_die(db_Node::get_node($node_id), "Unable to find node for id %s", $node_id);
            
            // Just to ensure we're not cycling the tree
            if ($node->id == $node_target->id || $node->ancestor_of($node_target)) {
                throw new Exception("Can't move ".$node->idstr()." under itself in ".$node_target->idstr());
            }
            
            $change_result->touch_region(Node_Change_Notice::structural_change_to($node->get_parent()));
            $node->parent_id = $node_target->id;
            $node->update();

            $nodenames[] = $node->get_contenttitle();
        }
        
        $change_result->touch_region(Node_Change_Notice::structural_change_to($node_target));
        
        $change_result->add_message("Moved ".count($selected)." nodes to ".$node_target->idstr());
        $change_result->touch_region('content');
    }
}

