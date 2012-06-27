<?php

function smarty_modifier_makeaction($val, $delimiter=":") {	
    require_once "lib/action.php";
    $action = false;
    if (is_string($val))
        $action = Action::build(split($delimiter, $val));
    elseif ($val instanceof BasicAction)
        $action = $val;
    return $action;
}
?>