<?php 
/** Function generating action buttons to change the activation status of the given object.
  * Params:
  *     action: the action for the button
  *     active: whether the object is currently active
  *     show_noedit: Whether to show an img without link if the action is false or cannot be built (true by default)
  */
function smarty_function_activationbutton($params, &$smarty) {
    $smarty->loadPlugin('smarty_modifier_makeaction');

    $action = smarty_modifier_makeaction(get($params, 'action'));
    if (get($params, 'active')) {
        $src = "picts/flag_1.gif";
        $de = "de";
        $in = "";
    } else {
        $src = "picts/flag_0.gif";
        $de = "";
        $in = "in";
    }
    
    if ($action) {
        $alt = $smarty->get_config_vars($de.'activate');
        return '<input type="image" name="'.str($action).'" src="'.$src.'" title="'.$alt.'" alt="'.$alt.'" class="imagebutton" />';
    } elseif (get($params, 'show_noedit', true)) {
        $alt = $smarty->get_config_vars($in.'active');
        return '<img src="'.$src.'" alt="'.$alt.'"/>';
    }
}
?>