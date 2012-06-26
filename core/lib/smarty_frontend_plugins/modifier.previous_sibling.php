<?php
/** Find previous sibling to given node */
function smarty_modifier_previous_sibling($node) {
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
        $previous = false;
        foreach($siblings as $sibling) {
            if ($sibling->id == $node->id) {
                return $previous;
            }
            $previous = $sibling;
        }
    }
    return false;
}
?>