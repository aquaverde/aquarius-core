<?php 
/** Displays a path to a node
  * e.g. Home >  Unsere Angebote >  Prävention
  * where the last node is the actual node and is not a link
  * Params:
  *   node:  until which node (normally the actual node)
  *   exclude: comma separated list of node-ids to exclude in the path
  */
function smarty_function_nodepath_html_title($params, &$smarty) {
    
    $nodeid = get($params, 'node',$smarty->get_template_vars('node'));
    $node = db_Node::get_node($nodeid);
    $exclude = explode(',', get($params, 'exclude',null));
    $lower = get($params, 'lower', false);
    $reverse = get($params, 'reverse', false);
    
    $path = "";
    $parents = $node->get_parents(true);
    $parents_ext = requestvar("parents") ;
        
    if ($parents_ext)
    {
    	$last = array_pop($parents) ; 
    	array_pop($parents) ; 
    	foreach (explode(",", $parents_ext) as $pe)
    	{
    		$pe_node = db_Node::get_node($pe);
    		if($pe_node) {
    			$parents[] = $pe_node ; 
    		}
    	}
    	$parents[] = $last ; 
    }

    if ($reverse) $parents = array_reverse($parents) ;    
    $titles = array();
    
    foreach($parents as $parent) {
        if(! in_array($parent->id, $exclude)) { 
            $titles[] = strip_tags($parent->get_title());
        }
    }
    
    $path = join(" | ", $titles);

    if ($lower) $path = strtolower($path) ;
    
    return strip_tags($path);
}
?>