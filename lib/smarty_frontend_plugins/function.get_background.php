<?php

/* 	
	- get backgrounds 
	- search down the tree
*/

function smarty_function_get_background($params, &$smarty) 
{ 
	$node = get($params, 'node') ;
	$shuffle = get($params, 'shuffle') ;
	
    if (!$node) $smarty->trigger_error("Node is missing for function picture_background") ;
    $background = $node->get_background() ; 
    $parent = $node->get_parent() ; 
    
    while (!$background) {
    	$background = $parent->get_background() ; 
    	$parent = $parent->get_parent() ; 
    }
    
    if ($shuffle) shuffle ($background) ;
        
    $smarty->assign('background', $background) ;
	return "" ; 
	
}

?>