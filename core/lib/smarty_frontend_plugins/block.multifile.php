<?
/** Loop over a multi-file field (or as well over a single-file field)
 *	Params:
 *    	node: The node which we are searching pointings for
 *    	field: The name of the field
 *   	lg: language
 *   	shuffle: randomize list before display
 *      limit: cuts the list after the given limit
 *      cleanlegend: dont display filemanes if legend is empty
*/

function smarty_block_multifile($params, $content, &$smarty, &$repeat) {
    static $files_list;
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
            $files_list = $content->$fieldname;
        } else {
            $files_list = array($content->$fieldname); //to treat them the same from now on
        }
        
        if (get($params, 'shuffle')) shuffle($files_list);
        
        // Limit length
        $limit = get($params, 'limit');
        if (is_numeric($limit)) {
            $files_list = array_slice($files_list, 0, $limit);
        }        
        
	}	

    // In each iteration, we check whether there's a file left to display, and load its content
    $file = array_shift($files_list);
    
    $repeat = (bool)$file;
    if ($repeat) {
        $smarty->assign('filename', $file['file']);
        if (get($params, 'cleanlegend')) {
            if ($file['legend']) $smarty->assign('legend', $file['legend']);
        }
        else {
            if ($file['legend']) $smarty->assign('legend', $file['legend']);
            else $smarty->assign('legend', basename($file['file']));
        }
        $smarty->assign('th', file_prefix($file['file'],"th_"));
        $smarty->assign('alt', file_prefix($file['file'],"alt_"));
    }
    
	return $content;
}
?>