<?php

/** 
  * counts the entries for a given form. optionally constrains to one lg
  *
  *	Required parameters: form_id (of the block) 
  * Optional parameters: lg, if not set counts entries for all lg's for this form 
  */
  
function smarty_function_dynform_count_entries($params, &$smarty) 
{    
	global $DB ; 
    $form_id = get($params, 'form_id', false) ;
    $lg = get($params, 'lg', false) ; 
    if (!$form_id) {
    	$smarty->trigger_error("dynform_count_entries: required parameter <b>form_id</b> missing") ;
    	return ;	
    }
   	$query = 'SELECT COUNT(*) FROM dynform_entry WHERE dynform_id='.$form_id.' ' ;
    if ($lg) $query .= 'AND lg="'.$lg.'"' ; 
	$res = $DB->listquery($query) ; 
	if (count($res)) return $res[0] ; 
	return 0 ; 
}
?>