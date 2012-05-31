<?
/** Formfield that offers selection of nodes
  * sup3 supplies a comma separated list of nodes available in the selection. Alternatively, set_selection() may be used to provide the list of nodes.
  */
class Formtype_nodelist extends Formtype {

    var $selection_nodes = false;

    /** Set list of available nodes in selection
      * @param $nodes Array of node ids
      */
    function set_selection($nodes) {
        $this->selection_nodes = $nodes;
    }

    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        global $aquarius;

        if (!$formfield->multi) throw new Exception("node selection field must always be set to multi, check form '".$formfield->get_form()->title."'");

        $valobject->node_options = array();
        foreach ($this->selection_nodes($formfield) as $selection_node) {
            $title = $selection_node->get_contenttitle();
            $selected = false;
            foreach($valobject->value as $selected_node) {
                if ($selection_node->id == $selected_node->id) $selected = true;
            }
            $valobject->node_options[$selection_node->id] = compact('title', 'selected');
        }
    }

    function post_contentedit($formtype, $field, $value, $node, $content) {
        return db_Node::get_nodes($value);
    }

    /** Load node object from id */
    function db_get($values, $formfield) {
        $selected_node = db_Node::get_node(first($values));
        if (!$selected_node) return null;
        return $selected_node;
    }

    function db_set($value, $formfield) {
        if (!$value instanceof db_Node) return null;
        foreach($this->selection_nodes($formfield) as $selectable_node) {
            if ($selectable_node->id == $value->id) return array($value->id);
        }
        return null;
    }

    function selection_nodes($formfield) {
        $selection_nodes = false;
        if ($this->selection_nodes) {
            $selection_nodes = force($this->selection_nodes);
        } else {
            $selection_nodes = array_map('trim', explode(',', $formfield->sup3));
        }
        return db_Node::get_nodes($selection_nodes);
    }
}
?>