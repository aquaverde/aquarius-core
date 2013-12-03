<?php 
/** @package Aquarius.frontend
  */

/** 
  *
  */


function smarty_function_calculate($params, &$smarty) {
    $element1 = doubleval(get($params,"element1"));
    $element2 = doubleval(get($params,"element2"));
    $op = str(get($params,"op"));
    $result = 0;

    switch ($op) {
        case "+":
            $result = $element1 + $element2;
            break;
        default:
            return;
    }
    $smarty->assign("calcresult",$result);

    
    return $result;
}
?>