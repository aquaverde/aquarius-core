<?php
/** Whether the given node has children */
function smarty_modifier_has_children($node) {
    $node = db_Node::get_node($node);
    return $node && $node->cache_left_index != $node->cache_right_index;
}
?>