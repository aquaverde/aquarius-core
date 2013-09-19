<?php
/** Get a content field for a node and assign it to var.
  * Parameters:
  *   @param node  A node object, id or name. Preset: current node.
  *   @param field Name of the content field to read, required.
  *   @param var Assign to this variable name instead of printing it
  *   @param lg Optional to select content language. Preset: current language.
  *   @param box Access content of box, instead of the content's node itself. Preset: no. Try to use inherit instead, it's more robust.
  *   @param inherit Search parents for a value. Preset: no.
  *   @param shuffle should the value be an array, shuffle it. Preset: no.
  *   @param pick Specify an array entry to pick should the field value be an array. May be 'first', 'random', or an index into the array. Preset: no picking
  * 
  * 
  * Simple example: Reading the root title.
  * 
  * <h1>{getfield node=root field=title}</h1>
  * 
  * Example of picking one of multiple header pictures. Option 'inherit' is
  * activated so that pictures of parent content are used should the current
  * content lack a header_picture:
  *
  * <div class='header'>
  * {getfield field=header_picture inherit=true pick=random var=headerpic}
  * {if $headerpic}
  *     <img src='{$headerpic.file|escape}' alt='{$headerpic.legend|escape}' />
  * {/if}
  * </div>
  * 
  */
function smarty_function_getfield($params, $smarty) {

    $node  = db_Node::get_node(get($params, 'node', $smarty->get_template_vars('node')));
    $field = get($params, 'field');
    $var   = get($params, 'var');
    $lg    = get($params, 'lg', $smarty->get_template_vars('lg'));
    $box   = get($params, 'box');
    $shuffle = (bool)get($params, 'shuffle', false);
    $pick  = get($params, 'pick', false);
       
    if (!$node) $smarty->trigger_error("load: require parameter node, either an node object, numeric node id or 'root'.");
    
    while($box && $node->is_boxed()) $node = $node->get_parent();
        
    if (!is_string($field)) $smarty->trigger_error("load: require parameter 'field', must be string");

    $try_nodes = array();
    if (get($params, 'inherit', false)) {
        $try_nodes = array_reverse($node->get_parents(true));
    } else {
        $try_nodes = array($node);
    }

    $value = null;
    foreach($try_nodes as $node) {
        $content = $node->get_content($lg);
        if ($content) {
            $content->load_fields();            
            if (isset($content->$field) && !empty($content->$field)) {
                $value = $content->$field;
                break;
            }
        }
    }
    if ($value !== null) {
        if (is_array($value) && $shuffle) {
            shuffle($value);
        }
        
        if (is_array($value) && $pick) {
            switch($pick) {
                case 'first':   $value = first($value); break;
                case 'random':  $value = $value[array_rand($value)];  break;
                default: $value = get($value, $pick);
            }
        }
            
        if ($var) {
            $smarty->assign($var, $value);
        } else {
            return $value;
        }
    }
    return "";
}
