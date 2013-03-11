<?php 
/** Function counting the use of one form in the structure
  * Params:
  *    form_id: id of the form to count the uses in the tree
  */
function smarty_function_count_form_used($params, &$smarty) {

    $form_id = get($params, 'form_id', null) ;
    if (!$form_id) $smarty->trigger_error("count_form_used: missing 'form_id' parameter") ;

    $node = DB_DataObject::factory('node') ;
	$node->form_id = $form_id ; 
	$count = $node->count() ; 
	
	$node = DB_DataObject::factory('node') ;
	$node->childform_id = $form_id ; 
	$count += $node->count() ; 
	
	$node = DB_DataObject::factory('node') ;
	$node->contentform_id = $form_id ; 
	$count += $node->count() ; 
	
	if ($count > 0) return $count ; 
	else return "-" ; 
}
?>