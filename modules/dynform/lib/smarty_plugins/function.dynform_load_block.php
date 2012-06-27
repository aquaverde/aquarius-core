<?
/** 
  * This loads a block for a given dynform. 
  * It assigns "block_fields".
  */
  
function smarty_function_dynform_load_block($params, &$smarty) 
{
    require_once $smarty->_get_plugin_filepath('modifier','makeaction') ;
    
    $smarty->assign('block_fields', null) ; 
    $block_id = get($params, 'block_id') ;
    
	$block = DB_DataObject::factory('dynform_block') ; 
	$block->id = $block_id ; 
	$found = $block->find() ; 

	if ($found) 
	{
		$block->fetch() ; 
		$fields = array() ;
		$dfield = DB_DataObject::factory('dynform_field') ; 
		$dfield->block_id = $block->id ; 
		$dfield->orderBy('weight ASC') ;
		$dfield->find() ; 
		while ($dfield->fetch()) {
			$fields[] = clone($dfield) ; 
		}
		$smarty->assign('block_fields', $fields) ; 
	}
}
?>