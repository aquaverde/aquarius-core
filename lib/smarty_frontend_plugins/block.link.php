<?php 
/** Make links to a node.
  * Convenience wrapper for the href plugin. This blocks wraps the content of the block in '<a>' tag with href attribute to the specified node. The block will not be executed for nonexistant nodes or languges.
  * Parameters:
  *   node: The node to link to
  *   lg: Which language to link. Default is the current language.
  *   on: A node. Links that lead to this node or one of its parents will get the second class. Template variable 'node' will be used if this is not specified.
  *   class: Class attribute of the a tag. A second class may be specified after a comma to be used when the link is 'on'
  *   loadcontent: load the content of the linked node inside the block, like {usecontent}
  *   data-*: data attributes to add to the link-tag
  *
  * The parameters of the href plugin can be used as well
  *
  * Example:
  *   {link node=123 class=",on"}go to node id 123{/link}
  * would be translated to something like:
  *   <a href=".../index.php?id=123">go to node id 123</a>
  */
function smarty_block_link($params, $content, $smarty, &$repeat) {
    static $lg;
    static $node;
    
    if ($repeat) {
    
        /* Initialization */
        $lg = db_Languages::validate_code(get($params, 'lg', $smarty->get_template_vars('lg')), $smarty->require_active);
        $node = db_Node::get_node(get($params, 'node'));

        // Execute block contents only when node and language are valid
        $repeat = (bool)($lg && $node && (!$smarty->require_active || $node->active()) && $node->get_content($lg, $smarty->require_active) );

        $smarty->loadPlugin('smarty_block_usecontent');
        if ($repeat && get($params, 'loadcontent')) smarty_block_usecontent($params, $content, $smarty, $repeat);

    } else {
        /* Wrap block content in <a> tag */

        // Generate href using the href plugin
        $params['node'] = $node;
        $params['lg'] = $lg;
        $smarty->loadPlugin('smarty_function_href');
        $href = smarty_function_href($params, $smarty);
        
        if (isset($params['on'])) {
            $on = db_Node::get_node($params['on']);
        } else {
            $on = $smarty->get_template_vars('node');
        }

        // The link is on if either the node we're linking to is the same that is $on or we're linking to one of its parents
        $active = $on && ($on->id == $node->id || indexOfAttr($on->get_parents(), 'id', $node->id) >= 0);
        
        // Determine the class of the link
        $classes = explode(',', get($params, 'class'));
        $normal_class = get($classes, 0);
        $on_class = get($classes, 1, $normal_class);
        $class = $active ? $on_class : $normal_class;
        $class_str = !empty($class) ? ' class="'.$class.'"' : '';

        // Data attributes
        $data_attrs = array();
        foreach($params as $name => $param) {
            if (strpos($name, 'data-') === 0) {
                $data_attrs []= "$name='".htmlspecialchars($param)."'";
            }
        }
        $data_str = '';
        if ($data_attrs) $data_str = ' '.join(' ', $data_attrs);
        
        // Wrap the content in a link
        $content = '<a href="'.$href.'"'.$class_str.$data_str.'>'.$content.'</a>';
        
        if (get($params, 'loadcontent')) smarty_block_usecontent($params, $content, $smarty, $repeat);
    }

    return $content;
}

