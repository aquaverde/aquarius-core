<?php
/** find an ancestor node's content of a given node at a given level
  * <pre>
  * Parameters:
  *  node: The node to be edited, $node template variable by default
  *  level: the level
  *  name: name of the variable to be assigned
  * </pre>
  */
function smarty_function_find_ancestor($params, &$smarty) {
    require_once('lib/db/Node.php');
    $nodeid = get($params, 'node', $smarty->get_template_vars('node'));
    $node = db_Node::get_node($nodeid);
    $level = get($params, 'level', null) ;
    if (!$level) {
        echo "Warning: Smarty function 'get_ancestor_at_level', missing parameter 'level'" ;
        return "" ;
    }
    $name = get($params, 'name', 'ancestor') ;
    $parents = $node->get_parents(true) ;
    if (count($parents) > $level) {
        $found_node = $parents[$level] ;
        $content = $found_node->get_content() ;
        if ($content) {
            $content->load_fields() ;
            $smarty->assign($name, $content) ;
        }
    }
}
?>