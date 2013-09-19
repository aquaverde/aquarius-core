<?php 
/** Make links to a node.
  * Convenience wrapper for the href plugin. This blocks wraps the content of the block in '<a>' tag with href attribute to the specified node. The block will not be executed for nonexistant nodes or languges.
  * Parameters:
  *   node: The node to link to
  *   lg: Which language to link. Default is the current language.
  *   on: A node. Links that lead to this node or one of its parents will have $on==true in the block. Template variable 'node' will be used if this is not specified.
  *   class: Class attribute of the a tag. A second class may be specified after a comma, it will be used if $on is true
  * The parameters of the href plugin can be used as well
  *
  * Note: Which class is used is decided after execution of the block content. Thus, you may reset the $on flag within the block to change the class.
  *
  * Example:
  *   {link node=123 class=",on"}go to node id 123{/link}
  * would be translated to something like:
  *   <a href=".../index.php?id=123">go to node id 123</a>
  */
function smarty_block_link($params, $content, &$smarty, &$repeat) {
    static $lg;
    static $node;
    
    if ($repeat) {
    
        /* Initialization */
        $lg = db_Languages::validate_code(get($params, 'lg', $smarty->get_template_vars('lg')));
        $node = db_Node::get_node(get($params, 'node'));

        if (isset($params['on'])) {
            $on = db_Node::get_node($params['on']);
        } else {
            $on = $smarty->get_template_vars('node');
        }

        // Execute block contents only when node and language are valid
        $repeat = (bool)($lg && $node && (!$smarty->require_active || $node->active()) && $node->get_content($lg, $smarty->require_active) );

        // The link is on if either the node we're linking to is the same that is $on or we're linking to one of its parents
        $smarty->assign('on', $on && ($on->id == $node->id || indexOfAttr($on->get_parents(), 'id', $node->id) >= 0));
        
    } else {
    
        /* Wrap block content in <a> tag */

        // Generate href using the href plugin
        $params['node'] = $node;
        $params['lg'] = $lg;
        $smarty->loadPlugin('smarty_function_href');
        $href = smarty_function_href($params, $smarty);

        // Determine the class of the link
        $classes = explode(',', get($params, 'class'));
        $normal_class = get($classes, 0);
        $on_class = get($classes, 1, $normal_class);
        $class = $smarty->get_template_vars('on') ? $on_class : $normal_class;
        $classstr = !empty($class) ? ' class="'.$class.'"' : '';

        // Wrap the content in a link
        $content = '<a href="'.$href.'"'.$classstr.'>'.$content.'</a>';
    }

    return $content;
}
?>