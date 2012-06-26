<?
/** Block to conditionally show actions. An action is built, and the content of the block executed only if the action is valid. Example:
    {action action="node:toggle_active:`$node->id`"}<a href="admin.php?{$action}">activate!</a>{/action}
Will only be shown if the user has permission to activate the node.

By default, the constructed action is assigned to $action in smarty, but the parameter 'var' can be used to override the name. As in {action action=$someaction var=myaction}.
*/
function smarty_block_action($params, $content, &$smarty, &$repeat) {
    require_once $smarty->_get_plugin_filepath('modifier','makeaction');
    // Don't change output
    if ($content)
        return $content;
    
    // Build action from action parameter
    $val = get($params, 'action');
    $var = get($params, 'var', 'action');
    $continue = get($params, 'continue');
    $delimiter = get($params, 'delimiter', ':');
    $action = false;

    if ($val) {
        $action = smarty_modifier_makeaction($val, $delimiter);
    }

    $smarty->assign($var, $action);

    // Show block contents only if $action is valid
    if (!$action && !$continue)
        $repeat = false;
}
?>