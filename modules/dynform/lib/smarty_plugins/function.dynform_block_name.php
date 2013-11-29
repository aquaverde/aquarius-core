<?php

/** 
  * This finds and returns the correct translation for a given block name. 
  *
  *	Required parameters: id (of the block) and lg 
  */
  
function smarty_function_dynform_block_name($params, &$smarty) 
{    
	require_once('lib/libdynform.php') ;  
    $bid = get($params, 'id') ;
    $lg = get($params, 'lg') ; 
    
    if (!$bid) $smarty->trigger_error("dynform_block_name: require parameter id missing") ;
    if (!$lg) $smarty->trigger_error("dynform_block_name: require parameter lg missing") ;
    
    $DL = new Dynformlib ; 
    return $DL->get_block_name($bid, $lg) ; 
}
?>