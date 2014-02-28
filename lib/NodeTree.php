<?php 
/** @package Aquarius */

/** Utility methods to load whole trees of nodes */
class NodeTree {
    /** Get tree representation in nested arrays
      * @param $node The root node of the tree (This node will always be returned, regardless of the filters)
      * @param $prefilters are passed to the Node::children() method (default: none)
      * @param $node_filter nodes that pass this filter will be included in the tree, children of filtered nodes will not be included either. (default: pass all)
      * @param $descend_filter only include children of a node if the node passes $descend_filter (default: pass all)
      * @param $purge_filter remove branches where no nodes pass the purge_filter. Note that those branches will still be loaded from the DB initially, it's better to use the other filter methods when possible. (default: pass all)
      * @param $max_depth limits the amount of levels returned, $max_depth=1 means just the children of $node (default: false <=> no limit)
      *
      * The returned tree looks like this:
      *    array(
      *      'node' => $node,
      *      'children' => array(
      *        array(
      *          'node' => $firstchild,
      *          'children' => array(...grandchildren...)
      *        ),
      *        array(
      *          'node' => $secondchild,
      *          'children' => array(...grandchildren...)
      *        )
      *      )
      *    )
      *
      * Invariant: Only childs are filtered, an entry for $node is always returned.
      */
    static function build($node, $prefilters = array(), $node_filter = false, $descend_filter = false, $purge_filter = false, $max_depth = false) {
        // Default filters
        if (!$prefilters)     $prefilters     = array();
        if (!$node_filter)    $node_filter    = NodeFilter::create('all', true);
        if (!$descend_filter) $descend_filter = NodeFilter::create('all', true);
        if (!$purge_filter)   $purge_filter   = NodeFilter::create('all', true);

        // List of childs that match the filters
        $children = array();

        // If we haven't reached the depth limit, and the node passes the descend_filter, we load the childs
        if ($max_depth !== 0 && $descend_filter->pass($node)) {
            // Tell node to give us its children
            $child_nodes = $node->children($prefilters, $node_filter);

            // Loop over them to create sub trees
            foreach($child_nodes as $child_node) {
                $child_tree = self::build($child_node, $prefilters, $node_filter, $descend_filter, $purge_filter, $max_depth - 1);

                // The node must pass the purge_filter or have childs to be included in the tree
                if (count($child_tree['children']) > 0 || $purge_filter->pass($child_node)) {
                    $children[] = $child_tree;
                }
            }
        }

        // Build an entry for this node
        return compact('node', 'children');
    }
       
    /** Create preorder list from tree, with information to display 'branches' */
    static function flatten($current, $nodeconnections = array(), $join = false) {
        $list = array();
        $connections = $nodeconnections; // Assign means copy for PHP arrays *cough*
        
        // Depending on the type of the last join, we push a blank or a line on the stack
        if ($join) {
            $connections[] = $join;
            if ($join == "joinbottom")
                $nodeconnections[] = "clear";
            else if ($join == "join")
                $nodeconnections[] = "line";
        }
        $node = $current['node'];
        $depth = count($connections);
        $list[] = compact('connections', 'node', 'depth');

        $children = $current['children'];
        $lastchild = end($children);
        // Add all childs to the list
        foreach($children as $child) {
            $join = "join";
            if ($child['node'] === $lastchild['node'])
                $join = "joinbottom";
            $list = array_merge($list, self::flatten($child, $nodeconnections, $join));
        }

        return $list;
    }

    /** Build tree and flatten it.
      * Same arguments as the build() method, same return type as the flatten_tree() method.
      * Convenience is king. */
    static function build_flat() {
        $args = func_get_args();
        return self::flatten(call_user_func_array(array('NodeTree', 'build'), $args));
    }

    /** Execute function for every node that satisfies filter in tree.
      * @param $tree Tree to walk
      * @param $func function to be executed on every node. First parameter is current node, second parameter the return value of the previous execution of $func.
      * @param $filter Only visit nodes that satisfy this filter, default: all.
      * @param $descend_filter Only visit childs of nodes that satisfy this filter, default: all.
      * @param $init initial value of the passed return value.
      * @param $preorder Set to false if you want postorder visiting.
      * @param $pass_container Set to true if you want the container to be passed to the function, instead of the node
      * @return return value of last execution of $func
      * This method may be used for reduction. For example, to count the nodes in the tree you would do:
      * <pre>
      *   $node_count = NodeTree::walk($tree, create_function('$node, $count', 'return $count + 1', false, false, 0);
      * </pre>
      */
    static function walk(&$tree, $func, $filter=false, $descend_filter=false, $init=null, $preorder=true, $pass_container=false) {
        $visit = !$filter || $filter->pass($tree['node']);
        $item = false;
        if ($visit) {
            if ($pass_container) {
                $item =& $tree;
            } else {
                $item = $tree['node'];
            }
        }
        if ($visit && $preorder) $init = call_user_func_array($func, array(&$item, $init));
        foreach ($tree['children'] as &$child) {
            if (!$descend_filter || $descend_filter->pass($child['node'])) $init = self::walk($child, $func, $filter, $descend_filter, $init, $preorder, $pass_container);
        }
        if ($visit && !$preorder) $init = call_user_func_array($func, array(&$item, $init));
        return $init;
    }


    /* Display functions */
    
    /** Add virtual 'new' nodes to children (This is a dummy object that has the 'newaction' property set to an action that will create a new child) */
    static function add_new(&$entry, $lg) {
        $node = $entry["node"];
        $newaction = Action::make("contentedit", "create", $node->id, $lg);
        if ($newaction) {
            $new = new stdClass();
            $new->newaction = $newaction;
            $entry['children'][] = array('node' => $new, 'children' => array());
        }
    }

    /** Get tree of nodes the user may edit
      * Loads tree of nodes that are permitted for editing. Nodes having children where the user may edit are included regardless of their own status.
      *
      * If the user may add nodes, a virtual 'new' node is added as child. These are dummy db_Node objects that have the 'newaction' property set to an action that will create a new child.
      *
      * @param $parent the parent of the children to be listed
      * @param $lg Language code for the 'new' actions
      * @param open_nodes List of node ids to be considered open, children of these are loaded as well
      * @return Tree in the format returned by the NodeTree class
      */
    static function editable_tree($root, $lg, $open_nodes) {
        global $aquarius;
        global $DB;


        // Build filters that pass the nodes we want to have in the tree

        // filter nodes the user may not expand
        $node_filter = false;

        $user = db_Users::authenticated();
        if (!$user->isSiteadmin()) {
            // Expand the list of nodes the user may edit to include all parent nodes
            $allowed_nodes = $DB->listquery("
                SELECT DISTINCT node_parent.id
                FROM node
                JOIN users2nodes ON node.id = users2nodes.nodeId
                JOIN node AS node_parent
                    ON node_parent.cache_left_index <= node.cache_left_index
                   AND node_parent.cache_right_index >= node.cache_right_index
                WHERE users2nodes.userId = $user->id
            ");

            $node_filter = NodeFilter::create('or', array(
                NodeFilter::create('ids', $allowed_nodes),
                NodeFilter::create('user_edit_permission', $user)
            ));
        }


        // pass nodes in the open_nodes list
        $descend_filter = NodeFilter::create('ids', $open_nodes);

        $tree = NodeTree::build($root, array(), $node_filter, $descend_filter);

        return $tree;
    }

    /** Attach a lot of stuff to tree node
      * Adds actions for each node to the tree. Inserts 'new' nodes where user may add nodes.
      *  @param entry NodeTree entry
      *  @param open_nodes Actions are only added for open nodes and their immediate children.
      *  @param type what controls to add. Either 'none', 'sitemap', 'contentedit' or 'super'
      *  @param add_new whether to add 'new' nodes
      *  @param lg language code of the language to be used for contentedit links
      */
    function add_controls(&$entry, $open_nodes, $type, $add_new, $lg) {
        $node = $entry['node'];
        $open = in_array($node->id, $open_nodes);

        $entry['title'] = strip_tags($node->get_contenttitle($lg));
        $entry['open'] = $open;
        
        if ($type == 'sitemap' || $type == 'super' || $type == 'contentedit') {
            $entry['has_content'] = (bool)$node->get_content($lg);
            $entry['may_change_weight'] = db_Users::authenticated()->may_change_weight($node, false);
            
            $entry['actions'] = array();
            $entry['actions']['toggle_active'] = Action::make('node', 'toggle_active', $node->id);
            $entry['actions']['contentedit'] = Action::make('contentedit', 'edit', $node->id, $lg);
            $entry['actions']['delete'] = Action::make('node', 'delete_dialog', $node->id);
            $entry['actions']['editprop'] = Action::make('node', 'editprop', $node->id);
        }
        
        if ($type == 'super') {
            $entry['actions']['toggle_lock'] = Action::make('node', 'toggle_lock', $node->id);

            // Links to forms specified for -- or inherited by -- this node
            $entry['forms'] = array();
            foreach(array('form', 'childform', 'contentform') as $formname) {
                $form = $node->get_form($formname);
                $data = array('form'=>$form);
                if ($form) {
                    $data['action'] = Action::make('formedit', 'edit', $form->id);
                    $data['class'] = $node->{$formname.'_id'}?'':'class="dim"'; // Dimm inherited forms
                }
                $entry['forms'][$formname] = $data;
            }
        }
        if ($open) {
            foreach($entry['children'] as &$child) {
                self::add_controls($child, $open_nodes, $type, $add_new, $lg);
            }
            if ($add_new) self::add_new($entry, $lg);
        }

        // Determine whether we show a open/close toggle
        if ($open) {
            $entry['show_toggle'] = !empty($entry['children']);
            if ($type != 'none') {
                $accepted_forms = $node->available_childforms();
                $accepted_ids = array();
                foreach($node->available_childforms() as $cf) {
                    $accepted_ids []= 'accepts_'.$cf->id;
                }
                $entry['accepted_children'] = join(' ', $accepted_ids);
            }
        } else {
            // There are two cases where we have to add a toggle
            //  - The node has children
            //  - The user may add children
            if ($node->cache_left_index != $node->cache_right_index) {
                // Node has children
                $entry['show_toggle'] = true;
            } else {
                // Rather than trying to determine whether a user is allowed to add children, we just ask contentedit
                $entry['show_toggle'] = $add_new && (bool)Action::make("contentedit", "edit", "new", $lg, $node->id);
            }
        }

    }

    function get_open_nodes($section) {
        global $aquarius;
        $open_nodes_sections = $aquarius->session_get('nodetree_open_nodes', array());

        $open_nodes = get($open_nodes_sections, $section, array());

        return $open_nodes;
    }
    
    function set_open_nodes($section, $nodes) {
        global $aquarius;
        $open_nodes_sections = $aquarius->session_get('nodetree_open_nodes', array());

        array_unique($nodes); // Remove duplicates
        $open_nodes_sections[$section] = $nodes;

        $aquarius->session_set('nodetree_open_nodes', $open_nodes_sections);
    }
}
