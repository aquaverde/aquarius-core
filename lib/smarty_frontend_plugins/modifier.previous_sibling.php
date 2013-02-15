<?php
/** Find previous sibling to given node */
function smarty_modifier_previous_sibling($loc) {
    $lg = false;
    if ($loc instanceof db_Content) {
        $lg = $loc->lg;
    }
    $node = db_Node::get_node($loc);

    $parent = false;
    if ($node) {
        $parent = $node->get_parent();
    }
    $siblings = false;
    if ($parent) {
        $contentfilter = false;
        if ($lg) $contentfilter = NodeFilter::create('has_content', $lg);
        $siblings = $parent->children(array('inactive'), $contentfilter);
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
