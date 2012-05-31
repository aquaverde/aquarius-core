<?php

/* 	
	- get backgrounds 
	- search down the tree
*/

function smarty_function_get_background($params, &$smarty) 
{ 
	$node = get($params, 'node') ;
	
    if (!$node) $smarty->trigger_error("Node is missing for function get_background") ;
    $flash = $node->get_flash() ;
    $picture_fixed = $node->get_picture_fixed() ;
    $parent = $node->get_parent() ; 
    
    while (!$flash && !$picture_fixed) {
    	$flash = $parent->get_flash() ;
    	$picture_fixed = $parent->get_picture_fixed() ; 
    	$parent = $parent->get_parent() ; 
    }
            
    $smarty->assign('flash', $flash) ;
    $smarty->assign('picture_fixed', $picture_fixed) ;
	return "" ; 
	
}

?>