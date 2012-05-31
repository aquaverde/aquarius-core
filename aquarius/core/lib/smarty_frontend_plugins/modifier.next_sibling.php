<?php
/** Find next sibling to given node
  * Usage example: {link node=$node|next_sibling}next example{/link}
  */
function smarty_modifier_next_sibling($node) {
    $node = db_Node::get_node($node);
    $parent = false;
    if ($node) {
        $parent = $node->get_parent();
    }
    $siblings = false;
    if ($parent) {
        $siblings = $parent->children(array('inactive'));
    }
    if (!empty($siblings)) {
        $found_self = false;
        foreach($siblings as $sibling) {
            Log::debug($sibling->cache_title);
            if ($found_self) {
                return $sibling;
            }
            $found_self = $sibling->id == $node->id;
        }
    }
    return false;
}
?>