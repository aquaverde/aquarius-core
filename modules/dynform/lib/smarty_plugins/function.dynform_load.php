<?php 
/** 
  * This loads a dynform for a given content. 
  * It assigns "dynform" and "blocks".
  * Both these assoc arrays just contain what is stored in the db for the given content.
  */
  
function smarty_function_dynform_load($params, &$smarty) 
{
    require_once $smarty->_get_plugin_filepath('modifier','makeaction') ;
    
    $smarty->assign('blocks', null) ; 
    $content_id = get($params, 'content_id') ;
    
   	$content = DB_DataObject::factory('content') ; 
    $content->id = $content_id ;
    
    $found = $content->find() ; 
    if ($found) $content->fetch() ; 
    $node_id = $content->node_id ;
    
    if (!$node_id) 
    {
    	$node_id = 0 ;  // this is required else find() takes the first dynform it can find when it's for a new node
    	$smarty->assign("unassigned_node", true) ; 	
    }
    
	$dynform = DB_DataObject::factory('dynform') ; 
	$dynform->node_id = $node_id ;
	$found = $dynform->find() ; 
	if ($found) 
	{
		$dynform->fetch() ; 
		$blocks = array() ;
		$dblock = new db_Dynform_block ;
		$dblock->dynform_id = $dynform->id ; 
		$dblock->orderBy('weight ASC') ;
		$dblock->find() ; 
		while ($dblock->fetch()) {
			$blocks[] = clone($dblock) ; 
		}
		$smarty->assign('blocks', $blocks) ; 
	}
	else { $dynform = null ; }
	
	$smarty->assign("dynform", $dynform) ;
}
?>