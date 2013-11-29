<?php 
/** Get random entry from list.
*Parameters:
*    list: List of entries
*    var: Name of the variable to assign, if var is not set, the field value is returned (printed)
*/

function smarty_function_random($params, &$smarty) {

    $list = get($params, 'list');
    $var = get($params, 'var');
       
    if (!is_array($list) || empty($list)) return "";
    
    $entry = $list[rand(0,count($list)-1)] ;
    
    if ($var)
        $smarty->assign($var, $entry);
    else
        return $entry;
    return "";
}
?>