<?php 
/** Special function for the forms manager "fm"
  * Load all dynforms that can be found in the dynform table.  
  * It assigns "dynforms"
  */
  
function smarty_function_dynform_fm_get_forms($params, &$smarty) 
{
	$lg = get($params, 'lg') ;
	if (!$lg) $smarty->trigger_error("dynform_fm_get_forms: required parameter lg missing") ;

	$dynforms = array() ;     
	$dynform = new db_Dynform ; 
	$found = $dynform->find() ; 
	if ($found) {
		while($dynform->fetch()) $dynforms[] = $dynform->toArray() ;
		
		for ($i = 0 ; $i < count($dynforms) ; $i++)
		{
			$node = new db_Node ; 
			$node->id = $dynforms[$i]['node_id'] ; 
			$found = $node->find() ; 
			if ($found)
			{
				$node->fetch() ; 
				$dynforms[$i]['title'] = $node->title ; 
				$content = $node->get_content($lg) ; 
				if ($content)
				{
					$content->load() ; 
					$dynforms[$i]['title'] = $content->title ;
					$dynforms[$i]['title2'] = $content->title2 ; 
					$dynforms[$i]['content_id'] = $content->id ; 
				}
			}
		}	
	}
	else { $dynforms = null ; }
	
	$smarty->assign("dynforms", $dynforms) ;
}
