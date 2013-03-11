<?php 
/** Loop over a multi-link field (or as well over a single-link field)
Params:
    node: The node which we are searching pointings for
    field: The name of the field
    lg: language
    shuffle: randomize list before display
*/

function smarty_block_multilink($params, $content, &$smarty, &$repeat) {
    static $links_list;
	global $DB;

    // On first invocation of the block, we build the list of nodes
	if ( $repeat ) {
        $nodeparam = get($params, 'node');
        $fieldname = get($params, 'field');
        $node = db_Node::get_node($nodeparam);
        if (!$node) $smarty->trigger_error("Could not load node from param ".$node);
        
        $lg = db_Languages::ensure_code(get($params, 'lg', $smarty->get_template_vars('lg')));

        // Look for pointings starting from this node
        $pointing_nodes = array();
        
        $content = $node->get_content($lg);
        $content->load_fields();
        $formfields = $content->get_formfields();
        if($formfields[$fieldname]->multi) {
            $links_list = $content->$fieldname;
        } else {
            $links_list = array($content->$fieldname); //to treat them the same from now on
        }
        
        if (get($params, 'shuffle')) shuffle($links_list);
	}	

    // In each iteration, we check whether there's a link left to display, and load its content
    $link = array_shift($links_list);
    
    $repeat = (bool)$link;
    if ($repeat) {
		
				
        if (1 == strpos($link['link'], '/', 1)) {
        	$cleanedLink = HOST.$link['link'] ;
        }
        elseif (0 == strpos($link['link'], 'http')) {
        	$cleanedLink = $link['link'] ;
        }     
		else {
        	$cleanedLink = "http://".$link['link'];
        }
        
        if ($link['text'] == "") $link['text'] = $cleanedLink;
        
        $smarty->assign('link', $cleanedLink);
        $smarty->assign('text', $link['text']);
        $smarty->assign('target', $link['target']);
    }
    
	return $content;
}
?>