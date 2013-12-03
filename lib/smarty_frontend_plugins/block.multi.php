<?php 
/** Loop over a multi field (or as well over a single-link field)
Params:
 	type: can be either "pics", "files", "links" - is mandatory
    field: The name of the field - is mandatory
    
Returns:
	Pics / Files: 
	file: the file 
	
	Links: 
	link: the link
*/

function smarty_block_multi($params, $content, &$smarty, &$repeat) {
    static $lists ;
    
    $know_types = array("pics", "files", "links") ; 

    // On first invocation of the block, we build the list of nodes
	if ( $repeat ) {
        $type = get($params, 'type') ;
        if (!$type) $smarty->trigger_error("Parameter 'type' is mandatory for block multi") ;
        if (!in_array($type, $know_types)) $smarty->trigger_error("Unknow 'type' in block multi: $type") ;
        $field = get($params, 'field') ;
        if (!$field) $smarty->trigger_error("Parameter 'field' is mandatory for block multi") ;
        $index = 0 ; 
        switch ($type) 
        {
        	case "pics":
        	case "files":
        		$smarty->assign('file', $field[$index]);
        		break ;
        	
        	case "links":
        		$smarty->assign('link', $field[$index]);
        		break ; 
        }
        
        $lists[] = compact('field', 'index', 'type');
	}	
	
	// Get the current environment
    extract(array_pop($lists));
	
	$repeat = (bool)$field[$index] ; 

    if ($repeat) {

        switch ($type) 
        {
        	case "pics":
        	case "files":
        		$smarty->assign('file', $field[$index]);
        		break ;
        	
        	case "links":
        		$smarty->assign('link', $field[$index]);
        		break ; 
        }
        
        $index++ ; 
        $lists[] = compact('field', 'index', 'type');
    }
    
	return $content;
}
?>