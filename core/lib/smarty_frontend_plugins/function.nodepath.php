<?
/** Displays a path to a node
  * e.g. Home >  Unsere Angebote >  Prävention
  * where the last node is the actual node and is not a link
  * Params:
  *   node:  until which node (normally the actual node)
  *   exclude: comma separated list of node-ids to exclude in the path
  */
function smarty_function_nodepath($params, &$smarty) {
    
    $nodeid = get($params, 'node',$smarty->get_template_vars('node'));
    $node = db_Node::get_node($nodeid);
    $exclude = explode(',', get($params, 'exclude',null));
    
    $path = "";
    $parents = $node->get_parents(true);
    require_once $smarty->_get_plugin_filepath('function','href');
    foreach($parents as $parent) {
        if(! in_array($parent->id, $exclude)) {
            // Generate href using the href plugin
            $hrefparams['node'] = $parent;
            $hrefparams['lg'] = $lg;
            $href = smarty_function_href($hrefparams, $smarty);
            if($node->id != $parent->id) {
                $path .= '<a href="'.$href.'">'.strip_tags($parent->get_title()).'</a> &gt;&nbsp;';
            } else {
                $path .= '<span class="on">'.strip_tags($parent->get_title()).'</span>';
            }
        }
    }
    return $path;
}
?>