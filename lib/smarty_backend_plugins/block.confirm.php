<?php 
/** Generate a 'wrapper action' that asks for confirmation before executing one of the contained actions.
  * Params:
  *   yes: Action to be executed when user clicks on yes (may be empty)
  *   no: action to be executed when user clicks on no (may be empty)
  *   title: Title of the confiramtion dialog
  *   message: Confirmation text
  *
  * The generated 'confirm' action instance is assigned to $action in the smarty container.
  * Content of the block is executed only if the 'yes' and 'no' actions are either empty or a permitted action.
  *
  * Usage example:
  *     {confirm yes="contentedit:delete:`$node->id`:`$content->lg`"
  *              no=''
  *              title="Really delete $node->title?"
  *              message=$smarty.config.s_confirm_delete_content|sprintf:$node->title}
  *         <a href="{url action1=$action}" title="{#s_delete_content#} [{$content->title}]"><img src="buttons/delete.gif" alt="{#s_delete_content#}" class="delete" /></a>
  *     {/confirm}
  */
function smarty_block_confirm($params, $content, &$smarty, &$repeat) {
    require_once $smarty->_get_plugin_filepath('modifier','makeaction');
    require_once $smarty->_get_plugin_filepath('function','url');
    // Don't change output
    if ($content)
        return $content;
        
    $title = get($params, 'title');
    $message = get($params, 'message');
    $yes = get($params, 'yes');
    $no = get($params, 'no');
    $delimiter = get($params, 'delimiter', ':');
    $var = get($params, 'var', 'action');
    
    $action = false;

    // Build the actions to validate the action string and test permissions
    // Empty actions are allowed
    $yes_action = false;
    $yes_valid = true;
    if (strlen($yes)) {
        $yes_action = smarty_modifier_makeaction($yes, $delimiter);
        $yes_valid = (bool)$yes_action;
    }
    $no_action = false;
    $no_valid = true;
    if (strlen($no)) {
        $no_action = smarty_modifier_makeaction($no, $delimiter);
        $no_valid = (bool)$no_action;
    }
    
    if ($yes_valid && $no_valid)
        $action = Action::make('confirm', $yes_action, $no_action, $title, $message);
    
    $smarty->assign($var, $action);
    if (!$action)
        $repeat = false;
}
?>