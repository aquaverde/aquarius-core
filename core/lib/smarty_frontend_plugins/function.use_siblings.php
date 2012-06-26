<?php
/** Load info about sibling nodes
  *
  * Params
  *   node: Optional, the node to load siblings foreach. Uses smarty node if not
  *         specified.
  *   var: Optional, Variable name to assign information to. Default is
           'siblings'.
  *
  * Loads the following information:
  *   nodes: entire list of siblings, including the node itself. Index starts at         
  *          one.
  *   next: next sibling from noded
  *   prev: previous sibling to node
  *   count: count of nodes loaded
  *   position: index of the current node, starting from one. 
  * 
  * Usage example: 
  *   {use_siblings}
  *   You are on page {$siblings.position} of {$siblings.count}. 
  *   {link node=$siblings.next}Next page: {$siblings.next->get_title()}{/link}
  *   {link node=$siblings.prev}Back: {$siblings.prev->get_title()}{/link}
  *
  */
function smarty_function_use_siblings($params, $smarty) {
    $nodedef = get($params, 'node', $smarty->get_template_vars('node'));
    $node = db_Node::get_node($nodedef);
    if (!$node) $smarty->trigger_error("Unable to load node '$nodedef'");
    $parent = false;
    if ($node) {
        $parent = $node->get_parent();
    }
    $nodes = false;
    if ($parent) {
        $nodes = $parent->children(array('inactive'));
    } else {
        // Lone root node
        $nodes = array($node);
    }
    $count = count($nodes);
    $next = false;
    $prev = false;
    $current_position = 0;
    $position = 0;
    $last = false;
    foreach($nodes as $sibling) {
        $current_position += 1;
        if ($sibling->id == $node->id) {
            $prev = $last;
            $position = $current_position;
        }
        if ($last && $last->id == $node->id) {
            $next = $sibling;
        }
        $last = $sibling;
    }
    $smarty->assign(get($params, 'var', 'siblings'), compact('nodes', 'next', 'prev', 'count', 'position'));
}
?>