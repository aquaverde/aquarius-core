<?php

/** 
  * This finds and returns the correct translation for a given block name. 
  *
  *	Required parameters: id (of the block) and lg 
  */
  
require_once('lib/libdynform.php') ;  

 
function smarty_function_dynform_field_name($params, &$smarty) 
{   
    $fid = get($params, 'id') ;
    $lg = get($params, 'lg') ; 
    $length = get($params, 'length', 85) ; 
    
    if (!$fid) $smarty->trigger_error("dynform_field_name: require parameter id missing") ;
    if (!$lg) $smarty->trigger_error("dynform_field_name: require parameter lg missing") ;
    
    $DL = new Dynformlib ;    
    return $DL->get_field_name($fid, $lg, $length) ; 
}
?>