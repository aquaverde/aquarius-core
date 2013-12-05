<?php 
/** Function generating action buttons to change the activation status of the given object.
  * Params:
  *     action: the action for the button
  *     active: whether the object is currently active
  *     show_noedit: Whether to show an img without link if the action is false or cannot be built (true by default)
  */
function smarty_function_activationbutton($params, &$smarty) {
    require_once $smarty->_get_plugin_filepath('modifier','makeaction');

    $action = smarty_modifier_makeaction(get($params, 'action'));
    if (get($params, 'active')) {
        $class = "on";
        $de = "de";
        $in = "";
    } else {
        $class = "off";
        $de = "";
        $in = "in";
    }
    
    if ($action) {
        $alt = $smarty->get_config_vars($de.'activate');
        return '<button name="'.str($action).'" class="btn btn-xs btn-link"><span class="glyphicon glyphicon-flag '.$class.'"></span></button>';
    } elseif (get($params, 'show_noedit', true)) {
        $alt = $smarty->get_config_vars($in.'active');
        return '<span class="glyphicon glyphicon-flag '.$class.'"></span>';
    }
}
?>