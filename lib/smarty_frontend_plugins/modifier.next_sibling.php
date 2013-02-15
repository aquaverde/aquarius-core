<?php
/** Find next sibling to given node
  * Usage example: {link node=$node|next_sibling}next example{/link}
  */
function smarty_modifier_next_sibling($loc) {
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
        $found_self = false;
        foreach($siblings as $sibling) {
            Log::debug("Sibling: ".$sibling->idstr());
            if ($found_self) {
                return $sibling;
            }
            $found_self = $sibling->id == $node->id;
        }
    }
    return false;
}
