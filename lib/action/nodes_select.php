<?php 
/** Node selection popup action
  * Disaplay a tree of nodes and let the user select nodes. There are two concrete actions: 'node_select_tree', and 'node_select_subtree', the first renders the full code, the second is used to get an open or closed subtree.
  * Action parameters:
  *   target_id: (only for 'node_select_tree')
  *       Reference ID from the opener.
  *
  *   lg:
  *       language for node titles
  *
  *   root_node:
  *       Id or name of the root node
  *
  *   depth:
  *       maximal depth of selection
  *
  *   exclude_levels:
  *       comma delimited list of levels where selection is prohibited
  *
  *   multi:
  *       Whether multiple selections are allowed
  *
  * An additional parameter may be given
  *
  *   - comma separated list of excluded nodes (these, and their children, will not show up in the tree)
  *
  * Request parameters:
  *   selected:
  *       comma-separated list of currently selected nodes
  *
  *   node:
  *       root node for the subtree ('node_select_subtree' action only)
  *
  *   open:
  *       whether to return an open or closed subtree ('node_select_subtree' action only)
  *
  * Whenever a selection changes, the popup calls the function 'nodes_selected' (or passed callback function) in the opener        with these parameters:
  *       target_id: The target id for the selection (the string that was passed as target id into the action)
  *       selected_nodes: A dictionary with selected nodes (key: node id, value: node title).
  */
class action_nodes_select extends AdminAction {

    /** Any user may get a node selection tree */
    function permit_user($user) {
        return (bool)$user;
    }

    function process_params($request) {
        $this->root_node = db_Node::get_node($this->root_id);
        if (!$this->root_node) throw new Exception("Node '$this->root_id' not found");

        $this->depth = intval($this->depth);
        $this->exclude_levels_list = array_map('intval', array_filter(explode(',', $this->exclude_levels), 'is_numeric'));
        $this->multi = (bool)$this->multi;

        $this->excluded_nodes = db_Node::get_nodes(explode(',', get($this->params, 0, '')));
        
        // TARGET ID
        if(isset($request['target_id'])) {
            $this->target_id = $request['target_id'];
        }
        
        $this->selected = get($request, 'selected');
        $this->selected_list = array_map('intval', array_filter(explode(',', $this->selected), 'is_numeric'));
        $this->selected_nodes = array();
        foreach($this->selected_list as $selected_id) {
            $selected_node = db_Node::get_node($selected_id);
            if ($selected_node && $this->selectable_node($selected_node)) { // Ignore invalid selected nodes
                $this->selected_nodes[$selected_node->id] = $selected_node;
            }
        }
    }

    /** Whether a node may be selected */
    function selectable_node($node) {
        foreach($this->excluded_nodes as $excluded_node) {
            if ($node == $excluded_node || $node->descendant_of($excluded_node)) return false;
        }
        $relative_depth = $node->depth() - $this->root_node->depth();
        return !in_array($relative_depth, $this->exclude_levels_list);
    }

    /** Get tree of nodes, open branches with selected descendants
      * With control settings added to each node in the tree, see add_control_settings.
      */
    function load_tree($root_node, $open_nodes, $visible_nodes, $closed_nodes=array()) {
        $max_depth = false;
        if ($this->depth) {
            $relative_depth = $root_node->depth() - $this->root_node->depth();
            $max_depth = $this->depth - $relative_depth;
        }

        $descend_filter = NodeFilter::create('and', array(
            NodeFilter::create('not', NodeFilter::create('nodes', $closed_nodes)),
            NodeFilter::create('or', array(
                NodeFilter::create('ancestor_of', $visible_nodes),
                NodeFilter::create('nodes', $open_nodes)
            ))
        ));

        $node_filter = false;
        if (!empty($this->excluded_nodes)) {
            $node_filter = NodeFilter::create('not', NodeFilter::create('nodes', $this->excluded_nodes));
        }

        $tree = NodeTree::build(
            $root_node,
            $prefilters = array(),
            $node_filter,
            $descend_filter,
            $purge_filter = false,
            $max_depth
        );


        $this->add_control_settings($tree);

        return $tree;
    }

    /** Add inforamtion about controls to show to the tree dicitionary
      * These entries are added:
      *   show_toggle: whether to show a toggle to open or close the branch of that node
      *   selectable: Whether the node is selectable
      *   selected: Whether the node is selected
      *   open: whether this branch has children
      *   title: Truncated title for this node
      */
    function add_control_settings(&$tree) {
        $node = $tree['node'];
        $relative_depth = $node->depth() - $this->root_node->depth();
        $has_children = NodeFilter::filter_has_children($node, true);
        $reached_max_depth = $this->depth > 0 && $relative_depth >= $this->depth;
        $tree['show_toggle'] = $relative_depth > 0 && !$reached_max_depth && $has_children;
        $tree['selectable'] = $this->selectable_node($node);
        $tree['selected'] = isset($this->selected_nodes[$node->id]);
        $tree['open'] = !empty($tree['children']);

        $title = $node->get_contenttitle($this->lg);
        if (strlen($title) > 60) {
            $title = mb_substr($title, 0, 60).'…';
        }
        $tree['title'] = $title;

        foreach($tree['children'] as &$child) {
            $this->add_control_settings($child);
        }

        // Hack: Add 'last' indicator to last entry in children. The reason we do this here is because the foreach we use in smarty does not easily provide this information when using the template recursively
        if (!empty($tree['children'])) {
            $tree['children'][count($tree['children'])-1]['last'] = true;
        }
    }
}

class action_nodes_select_tree extends action_nodes_select implements DisplayAction {
    var $props = array('class', 'command', 'target_id', 'lg', 'root_id', 'depth', 'exclude_levels', 'multi');

    function get_title() {
        return new Translation($this->multi ? 's_pointings_select' : 's_pointing_select');
    }

    /** Any user may get a node selection tree */
    function permit_user($user) {
        return (bool)$user;
    }

    function process($aquarius, $request, $smarty, $result) {
        $this->process_params($request);

        $subtree_action = Action::make('nodes_select', 'subtree', $this->lg, $this->root_id, $this->depth, $this->exclude_levels, $this->multi);

        $visible_nodes = $this->selected_nodes;
        $open_nodes = array($this->root_node);
        $selection_tree = $this->load_tree($this->root_node, $open_nodes, $visible_nodes);
        
        // Turn list of selected node ids into javascript hash
        $selected = array();
        foreach($this->selected_nodes as $selected_node) {
            $selected[] = "$selected_node->id: ".json($selected_node->get_contenttitle($this->lg));
        }
        $selected_str = '{'.join(',', $selected).'}';
        
        $smarty->assign('htmltitle', $this->get_title());
        $smarty->assign('target_id', $this->target_id);
        $smarty->assign('subtree_action', $subtree_action);
        $smarty->assign('entry', $selection_tree);
        $smarty->assign('selected', $selected_str);
        $smarty->assign('multi', $this->multi);
		
        $result->use_template('nodes_select.tpl');
    }
}

class action_nodes_select_subtree extends action_nodes_select implements DisplayAction {
    var $props = array('class', 'command', 'lg', 'root_id', 'depth', 'exclude_levels', 'multi');

    /** Any user may get a node selection tree */
    function permit_user($user) {
        return (bool)$user;
    }

    function process($aquarius, $request, $smarty, $result) {
        $subroot_id = get($request, 'node');
        $subroot = db_Node::get_node($subroot_id);
        if (!$subroot) throw new Exception("Subroot node '$subroot_id' not found");

        $this->process_params($request);

        $visible_nodes = $this->selected_nodes;
        $open_nodes = array();
        $closed_nodes = array();
        if (get($request, 'open')) {
            $open_nodes[] = $subroot;
        } else {
            $closed_nodes = array($subroot);
        }

        $selection_tree = $this->load_tree($subroot, $open_nodes, $visible_nodes, $closed_nodes);

        $smarty->assign('entry', $selection_tree);
        $smarty->assign('multi', $this->multi);

        $result->use_template('nodes_select_container.tpl');
    }
}
