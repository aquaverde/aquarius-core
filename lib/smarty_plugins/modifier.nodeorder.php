<?php
/** Sort nodes in node-tree order
  * Form-specific orderings by file are not supported */

function smarty_modifier_nodeorder($nodes) {
    $nodes = db_Node::get_nodes($nodes);
    usort($nodes, 'smarty_modifier_nodeordercmp');
    return $nodes;
}

function smarty_modifier_nodeordercmp($node1, $node2) {
    if ($node1->parent_id != $node2->parent_id) {
        return $node1->cache_left_index -  $node2->cache_left_index;
    }
    
    // Not what it's made for but here we go, should do content-sort
    return $node1->weight - $node2->weight;
}