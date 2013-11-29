<?php
/** Add "item$node_id" as anchor text instead of using the urltitle for
  * parent-fallthrough URL. Some old templates use this schema. */
class legacy_parent_fallthrough_anchor extends Module {
    var $short          = "legacy_parent_fallthrough_anchor";
    var $name           = 'Use \'item$node_id\' as anchor text for parent-fallthrough URL.';
    var $register_hooks = array('frontend_extend_uri_factory');

    function frontend_extend_uri_factory($url_factory) {
        $url_factory->add_step('relocate_for_parent_fallthrough', array($this, 'relocate_for_parent_fallthrough'));
    }

    /** If the target_node falls through to parent, use the parent as
      * target_node and add an anchor to the current node.
      *
      * The string 'item' followed by the node id of the original node is
      * added as anchor. */
    static function relocate_for_parent_fallthrough($options, $uri) {
        // Check whether relocation is necessary
        $node = $options->target_node;
        $relocate_node = $node;
        $relocated = false;
        while(true) {
            $form = $relocate_node->get_form();
            if ($form->fall_through == 'parent') {
                if ($relocate_node->is_root()) {
                    throw new Exception("Fall through to parent set in form of root node");
                }
                $relocated = true;
                $relocate_node = $relocate_node->get_parent();
            } else {
                break;
            }
        }
        
        if ($relocated) {
            // Add anchor to the original node
            $uri->anchor = 'item'.$node->id;

            $options->target_node = $relocate_node;
        }
    }
}

