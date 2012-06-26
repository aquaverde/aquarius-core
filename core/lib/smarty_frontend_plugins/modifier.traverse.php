<?php
/** Take the next node in the tree from the given node.
  * This allows preorder tree traversals. 
  * Inactive and fallthrough nodes are skipped.
  *
  * Params:
  *   node: The node to start from
  *   options: right now there is only one option: 'backwards'
  *
  * example:
  * {link node=$node|traverse:backwards}go to previous{/link} or {link node=$node|traverse}go to next{/link}
  */
function smarty_modifier_traverse($node, $options=false) {
    $node = db_Node::get_node($node);
    $backwards = $options=='backwards';

    $keep_going = true;
    while($keep_going) {
        echo "<br/>".$node->get_title();
        if ($backwards) {
            $where = "cache_left_index < $node->cache_left_index ORDER BY cache_left_index DESC";
        } else {
            $where = "cache_left_index > $node->cache_left_index ORDER BY cache_left_index ASC";
        }
        global $DB;
        $node_id = $DB->singlequery("SELECT id FROM node WHERE cache_active AND $where LIMIT 1");
        $node = db_Node::get_node($node_id);
        if (!$node) return false;
        $form = $node->get_form();
        $keep_going = (bool)$form->fall_through;
    }
    return $node;
}
?>