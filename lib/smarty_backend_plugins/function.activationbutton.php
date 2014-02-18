<?php 
/** Function generating action buttons to change the activation status of the given object.
  * Params:
  *     action: the action for the button
  *     active: whether the object is currently active
  *     show_noedit: Whether to show an img without link if the action is false or cannot be built (true by default)
  */
function smarty_function_activationbutton($params, $smarty) {
    $smarty->loadPlugin('smarty_modifier_makeaction');

    $action = smarty_modifier_makeaction(get($params, 'action'));
    if (get($params, 'active')) {
        $class = "on";
        $class2 = "off";
        $de = "de";
        $in = "";
    } else {
        $class = "off";
        $class2 = "on";
        $de = "";
        $in = "in";
    }
    
    if ($action) {
        $alt = $smarty->get_config_vars($de.'activate');
        
        if (get($params, 'show_title', false))
            return '<button name="'.str($action).'" class="btn btn-link"><span class="glyphicon glyphicon-flag '.$class2.'"></span>'.$alt.'</button>';
        else
            return '<button name="'.str($action).'" class="btn btn-xs btn-link" data-toggle="tooltip" title="'.$alt.'"><span class="glyphicon glyphicon-flag '.$class.'" title="'.$alt.'"></span></button>';
            
    } elseif (get($params, 'show_noedit', true)) {
        $alt = $smarty->get_config_vars($in.'active');
        return "<span class='glyphicon glyphicon-flag $class' title='$alt'></span>".$alt;
    }
}
