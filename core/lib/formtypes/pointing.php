<?
/** Pointings are references from content to nodes
  * They are used as cross-links between pages, or to categorize content.
  *
  * sup1:
  *    Enable a drop-down for selection directly from contentedit. Not available for multi fields
  *
  * sup2:
  *    name or id of root node for tree from which pointings may be selected
  *
  * sup3:
  *    maximal depth of selection tree
  *
  * sup4:
  *    comma-delimited list of depth where selection is prohibited
  */
class Formtype_Pointing extends Formtype {
    /** For single pointings with sup1 set, a flat tree is loaded to 'nodelist', so that a selection can be shown. In this case, the following keys will be set for the field: 'show_selection', 'disabled_depths', and 'nodelist'. Otherwise, the key 'selected_titles' will contain the list of selected nodes (for single pointings too); the key 'popup_action' will be the action to be used for node selection in a popup; and the key 'selected_ids' will be a comma delimited list of node identifiers.
     */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        $rootid = $formfield->sup2;
        if (empty($rootid)) {
            $rootnode = db_Node::get_root();
        } else {
            $rootnode = db_Node::get_node($rootid);
            if (!$rootnode) throw new Exception("Invalid pointing root node '$rootid' in formfield $formfield->id (sup2)");
        }

        // Depth of selection tree
        $depth = intval($formfield->sup3);
        if ($depth < 1) $depth = false;
        
        $valobject->disabled_depths = array();
        if($formfield->sup4 != "") {
            $valobject->disabled_depths  = split(",", $formfield->sup4);
        }
        $valobject->disabled_depths []= 0; // Root may never be selected

        $valobject->show_selection = $formfield->sup1 && !$formfield->multi;
        if ($valobject->show_selection) {
            $valobject->nodelist = NodeTree::build_flat($rootnode, array(), false, false ,false, $depth);
        } else {
            // List of selected_nodes (wrap single values in array for uniformity)
            if ($formfield->multi) {
                $valobject->selected_nodes = $valobject->value;
            } else {
                $valobject->selected_nodes = $valobject->value ? array($valobject->value) : array();
            }
            $selected_ids = array();
            foreach($valobject->selected_nodes as $node) $selected_ids[] = $node->id;
            $valobject->selected_ids = join(',', $selected_ids);

            $valobject->popup_action = Action::make('nodes_select', 'tree', $valobject->htmlid, $content->lg, $rootnode->id, $depth, $formfield->sup4, $formfield->multi);
        }

    }

    /** Turn comma delimited list into list of nodes, sort by tree order */
    function post_contentedit($formtype, $field, $value, $node, $content) {
        if ($field->multi) {
            $load_node = array('db_Node', 'get_Node');
            $value = array_filter(array_map($load_node, explode(',', $value)));
            usort($value, array($this, 'tree_order_compare'));
        } else {
            $value = db_Node::get_node($value);
        }
        return $value;
    }

    function tree_order_compare($node1, $node2) {
        return $node1->cache_left_index - $node2->cache_left_index;
    }

    /** Load node object from id */
    function db_get($values, $formfield) {
        $pointing_node = db_Node::staticGet(first($values));
        if (!$pointing_node) return null;
        return $pointing_node;
    }

    /** Save node id to DB.
      * If value is not a node object, this will try to load the node first and then save the id of the array. If the value is not a valid node identifier, it is ignored. */
    function db_set($value, $formfield) {
        $node = db_Node::get_Node($value); // Make sure it is a node object
        if ($node) return array($node->id);
        else return array();
    }
    
    function to_string($values) {
        if (!is_array($values)) {
            $values = array($values);
        }
        $strs = array();
        foreach($values as $value) {
            $node = db_Node::get_Node($value);
            if ($node) $strs []= $node->get_title();
        }
        return join(', ', array_filter($strs));
    }
}
?>