<?php
/** Load a node by id and assign its content to $name and the node itself to $name_node
  * Params:
  *   node: The thing to load
  *   var: the name of the variable the loaded node will be assigned to
  *   lg: optional language code of the content to be loaded
  *   ignore: Disable check that the node was loaded. Preset: false;
*/

function smarty_function_load($params, &$smarty) {

    $node = db_Node::get_node(get($params, 'node'));
    $var = get($params, 'var');
    $lg = get($params, 'lg', $smarty->get_template_vars('lg'));
    $ignore = get($params, 'ignore', false);

    if (!($node || $ignore)) {
        throw new Exception("load: could not load node '".get($params, 'node')."'");
    }

    if (!is_string($var)) {
        throw new Exception("load: require parameter var, must be string");
    }

    if ($node) {
        $content = $node->get_content($lg);
        if($content) $content->load_fields();
        
        $smarty->assign($var.'_node', $node);
        $smarty->assign($var, $content);
    }
}
