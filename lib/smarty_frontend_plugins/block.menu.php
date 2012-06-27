<?
/** @package Aquarius.frontend */



// Helper function to flatten a node tree, inserts dummy elements 'levelhead=true' and 'levelfoot=true' between parents and childs.
function flatten_nestednodes($entry, $active_nodes, $purge, $lg, $depth=0) {
    $list = array();
    $activechild = false;
    $levelhead = true;
    $levelfoot = false;
    $first = true;
    $last = false;
    $list[] = compact('depth', 'levelhead', 'levelfoot', 'first', 'last');
    $levelhead = false;
    $lastnode = end($entry['children']);
    foreach($entry['children'] as $child_entry) {
        $node = $child_entry['node'];
        $content = $node->get_content($lg);
        if ($content) {
            $content->loadcontent();
            $last = $node === $lastnode['node'];
            $active = in_Array($node->id, $active_nodes);
            $list[] = compact('depth', 'node', 'content', 'levelhead', 'levelfoot', 'active', 'first', 'last');
            $first = false;
            if (
                !empty($child_entry['children'])
                && (!$purge || $active) // If $purge is true, the child entry must be active to be included in the menu
            ) {
                $list = array_merge($list, flatten_nestednodes($child_entry, $active_nodes, $purge, $lg, $depth+1));
            }
        }
    }
    $levelfoot = true;
    $list[] = compact('depth', 'levelhead', 'levelfoot', 'first', 'last');
    return $list;
}

/** Block to build a nested menu.
  * Content of the block is repeated for each menu entry. To enable creating nested structures, dummy entries are inserted between level changes. On each iteration of the block, the variable $entry will be assigned the current node data as associative array, containing the fields:
  *    depth: the depth (relative to the menu root) of the element
  *    node: The node of the entry
  *    content: the content of the entry
  *    levelhead: True if the menu descends one level, signals a dummy element
  *    levelfoot: True if the menu ascends a level, signals a dummy element
  *    active: This node is active
  *    first: True if this item is the first entry in the group
  *    last: True if its the last entry in the group
  *    include_inactive: show nodes even if their parents are inactive, default false
  * The levelhead and levelfoot dummy entries have only the 'depth', 'levelhead' and 'levelfoot' fields set.
  *
  * Params:
  *   root: Start with the childs of this node in the menu (defaults to root)
  *   tree: Use instead of root if you already have a tree, in this case parameters root, hide depth &c. do not apply
  *   boxed: Include boxed items, (false by default)
  *   active: Which node is to be considered active, this node and all its parents will have the 'active' flag set
  *   purge: Remove menu groups that do not contain an active node from the menu, the first level is always shown. (Defaults to false)
  *   hide: List of nodes to exclude from the menu
  *   hidechildren: List of nodes whose children will not be shown (DEPRECATED name 'hidechilds' accepted as well)
  *   depth: The maximal depth of the menu (unlimited by default)
  *   hide_restricted: Do not include nodes where user must login to see them
  *
  * Example:
  *     {menu active=$node hide=53,60,63 boxed=true depth=2 root=$sub_root}
    {strip}
        {if $entry.levelhead}
            <ul id="navig-level{$entry.depth}">
        {/if}
        {if $entry.node}
            {if !$entry.first}</li>{/if}
            <li{if $entry.active} class="on"{/if}>
                {link node=$entry.node}{$entry.content->title}{/link}
        {/if}
        {if $entry.levelfoot}
        		</li>
            </ul>
            {if $entry.depth > 0}</li>{/if}
        {/if}
    {/strip}
{/menu}
  */
function smarty_block_menu($params, $content, &$smarty, &$repeat) {
    // We need to preserve state between calls to this function, but a menu block might be nested in another menu block. Thus $nodelists is a stack where the end($nodelists) element is the state for the current block.
    static $nodelists = array();
    
    // On first invocation get elements for menu
    if ($repeat) {
        $tree = get($params, 'tree');
        if (!$tree) {
            // Build tree   
            $root = db_Node::get_node(get($params, 'root'));
            if (!$root)
                $root = db_Node::get_root();
            
            $boxed      = get($params, 'boxed');
            $purge      = get($params, 'purge');
            $hide       = get($params, 'hide');
            $hidenodes = db_Node::get_nodes($hide);
            $show       = get($params, 'show');
            $depth      = get($params, 'depth');
            $active     = get($params, 'active');
            $include_inactive = get($params, 'include_inactive');
            $lg         = $smarty->get_template_vars('lg');
            
            $hideids = array();
            foreach($hidenodes as $hidenode) {
                $hideids[] = $hidenode->id;
            }

            $filters = array(
                NodeFilter::create('has_content', $lg), 
                NodeFilter::create('show_in_menu', true)
            );


            $prefilters = array();
            if ($include_inactive) {
                array_unshift($filters, NodeFilter::create('active_self', true));
            } else {
                $prefilters []= 'inactive';
            }

            if (!$boxed)    $prefilters[] = 'boxed';
            if ($hide)      $filters[] = NodeFilter::create('not', NodeFilter::create('ids', $hideids));

            $descend_filters = array();

            $hidechildren = get($params, 'hidechildren', get($params, 'hidechilds'));
            if ($hidechildren) {
                $children = db_Node::get_nodes($hidechildren);
                $ids = array();
                foreach($children as $child) {
                    $ids[] = $child->id;
                }
                if (!empty($ids)) {
                    $descend_filters[] = NodeFilter::create('not', NodeFilter::create('ids', $ids));
                }
            }

            $restriction_filter = new NodeFilter_Login_Required($smarty->get_template_vars("user"));
            if (get($params, 'hide_restricted')) {
                $filters []= $restriction_filter;
            } else {
                $descend_filters []= $restriction_filter;
            }

            $filter = NodeFilter::create('and', $filters);
            $descend_filter = false;
            if (!empty($descend_filters)) {
                $descend_filter = NodeFilter::create('and', $descend_filters);
            }

            $tree = NodeTree::build($root, $prefilters, $filter, $descend_filter, false, $depth);
        }

        // Get node IDs to be marked as active
        if ($active)
            $active_nodes = array_map(create_function('$node', 'return $node->id;'), $active->get_parents(true));
        else
            $active_nodes = array();
        
        // Flatten tree into one array
        $list = flatten_nestednodes($tree, $active_nodes, $purge, $lg);
        // Put the menu elements on the stack
        $nodelists[] = $list;
    }
    
    // Usually, we want to repeat
    $repeat = true;
    
    // Get the next menu element from the list on top of the stack
    $nodelist = array_pop($nodelists);
    $entry = array_shift($nodelist);
    array_push($nodelists, $nodelist);
   
   
    // At the end of the list, stop processing
    if (!is_array($entry)) {
        array_pop($nodelists); // Remove from stack
        $repeat = false;
    }
    
    // Assign the current nodeinfo
    $smarty->assign('entry', $entry);

    return $content;
}
?>