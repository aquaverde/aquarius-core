<?
/** Load Icon-Types for google maps
*/

function smarty_function_load_icon_types($params, &$smarty) {  

    $node = get($params, 'node');

    if (!$node) $smarty->trigger_error("load_icon_types: required 'node' parameter not set.");

    $rootnode =& db_Node::get_node($node);
    $nodelist = NodeTree::build_flat($rootnode, array('active'));
	
	$my_icons = array();
	
    foreach ($nodelist as $nodeinfo) {
		$content = $nodeinfo['node']->get_content();
		$content->load_field();
		$symbol = $content->symbol();
		$symbol = $symbol['file'];
		
		if(!empty($symbol)) $my_icons[$nodeinfo['node']->id] = $symbol;
    }

	$smarty->assign('my_icons', $my_icons);
}
?>