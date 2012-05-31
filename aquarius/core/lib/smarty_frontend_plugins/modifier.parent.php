<?php
/** Find parent node to given node
  * Usage example: {link node=$node|parent}parent node{/link}
  */
function smarty_modifier_parent($node) {
    $node = db_Node::get_node($node);
    $parent = false;
    if ($node) {
        return $node->get_parent();
    }
}
?>