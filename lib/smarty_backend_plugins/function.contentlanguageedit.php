<?php 
/** Function generating action links for different languages.
  * Params:
  *    node: id of the node to be edited
  *    currentlg: language id of the current lang
  *    class: optional class attribute of the generated a tags 
  *    return: Whether to include the current action in links (default true)
  *    assign: optional parameter to assign links to a variable name instead of returning a string
  * The function shows nothing if there's only one lang.
  */
function smarty_function_contentlanguageedit($params, &$smarty) {
    $smarty->loadPlugin('smarty_modifier_makeaction');
    
    $node_id = get($params, 'node');
    $currentlg = get($params, 'currentlg');
    if (!intval($node_id)) $smarty->trigger_error("content-language-edit: missing 'node' parameter, got '$nodeid'");
    $class = get($params, 'class');

    
    $node = DB_DataObject::factory('node');
    $node->id = $node_id;
    
    $links = array();
    foreach(db_Languages::getLanguages() as $lang) {
        $content = $node->get_content($lang->lg, false);
        $lgaction = Action::make('contentedit', 'edit', $node->id, $lang->lg);
        if ($lgaction) {
        
            $actionparams = array('action0'=>$lgaction);
            if (get($params, 'return', true)) $actionparams['action1'] = $smarty->get_template_vars('lastaction');
            
            $lglink = smarty_function_url($actionparams, $smarty);

            $link_class = $class;
            if ($lang->lg==$currentlg) $link_class .= ' active';
            if (!$content) $link_class .= ' dim';
            $link = '<a href="'.$lglink.'" class="'.$link_class.'" title="'.$lang->name.': '.$smarty->get_config_vars("s_edit").'">'.$lang->lg.'</a>&nbsp;';
            if (ADMIN_SHOW_CONTENT_ACTIVE_FLAGS) {
                if ($content) {
                    $lgtoggle = Action::make("contentedit", "toggle_active", $node->id, $lang->lg);
                    if ($content->active) { $class = "on"; } else { $class = "off"; }
                    $link .= '<button name="'.str($lgtoggle).'" class="btn btn-xxs btn-link" title="'.$lang->name.': '.$smarty->get_config_vars("content_tooltip_active").'"><span class="glyphicon glyphicon-flag '.$class.'"></span></button>';
                } else {
                    $link .= '<span class="glyphicon glyphicon-flag dim"></span>';
                }
            }
            $links []= $link.'&nbsp;&nbsp;';
        }
    }

    $assign = get($params, 'assign');
    if ($assign) {
        $smarty->assign($assign, $links);
    } else {
        return (count($links) > 1) ? implode(" ",$links) : '';
    }
}
?>