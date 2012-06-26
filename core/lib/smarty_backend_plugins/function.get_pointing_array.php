<?
/** Create the dropdown menu for single pointings
*/

function smarty_function_get_pointing_array($params, &$smarty) {  
    //require_once "lib/db/Node.php";

    $node = get($params, 'node');

    if (!$node) $smarty->trigger_error("get_pointing_array: required 'node' parameter not set.");

    $rootnode =& db_Node::get_node($node);
    $nodelist = NodeTree::build_flat($rootnode, array('active'));
	
	$returner = array();
	
	$returner[] = array("value" => "", "name" => $smarty->get_config_vars("select_pointing"));
	
    foreach ($nodelist as $nodeinfo) {
		$id = $nodeinfo['node']->id;
		$name = $nodeinfo['node']->get_contenttitle();
		
		$entry = array("value" => $id, "name" => str_repeat("--", count($nodeinfo['connections'])).$name);
		$returner[] = $entry;
    }

	$smarty->assign("my_pointing", $returner);
}
?>