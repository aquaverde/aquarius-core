<?php 
/** Create the dropdown menu for single pointings
*/

function smarty_function_select_pointings($params, &$smarty) {  
    //require_once "lib/db/Node.php";

    $node = get($params, 'node');
    $name = get($params, 'name');
    $selected = db_Node::get_node(get($params, 'selected'));

    if (!$node) $smarty->trigger_error("single_pointing: required 'node' parameter not set.");
    if (!$name) $smarty->trigger_error("single_pointing: required 'name' parameter not set.");
	
	if(is_string($node)) $rootnode =& db_Node::get_node($node);
    else $rootnode =& db_Node::staticGet($node);
    $nodelist = NodeTree::build_flat($rootnode, array('active'));

    $out = '<select name="'.$name.'">';
	if(get($params, 'gmap_selecter')) $out = '<select name="'.$name.'" onchange=change_marker_icon('.get($params, 'marker_id').',this.value);>';
    $out.= '<option>'.$smarty->get_config_vars("select_pointing").'</option>';
		
    foreach ($nodelist as $nodeinfo) {
        $sel = '';
        if ($selected && $selected->id === $nodeinfo['node']->id ) $sel = 'selected="selected"';

        $out .= '<option value="'.$nodeinfo['node']->id.'" '.$sel.'>'
               .str_repeat("&nbsp;&nbsp;", count($nodeinfo['connections']))
               .$nodeinfo['node']->get_contenttitle()
               .'</option>';		
    }

    $out .= '</select>';
	
    return $out;
}
?>