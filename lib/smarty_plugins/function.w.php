<?php 
/** get the wording data by a key.

    if additional parameters are passed, the translation is passed to
    printf() with those parameters.

    params:
        key         key for the wording you want to get
        lg          language you want (optional)
        escape      whether to escape output for HTML, on by default
        p*          all parameters having initial letter 'p' are passed to printf()
    
    Examples:
    
        {w key="%f percent of all statistics are made up on the spot" p=78.4}
        {w key="%d + %d is %d" p1=1 p2=3 p3=4}
    
*/
function smarty_function_w($params, &$smarty) {
	require_once "lib/db/Wording.php";
	
	$key = get($params, 'key');
	$lg = get($params, 'lg');
	$escape = get($params, 'escape', true);
	
	$printf_args = array();
	foreach($params as $name => $param) {
        if ($name[0] == 'p') $printf_args []= $param;
	}

    if (!$key) $smarty->trigger_error("w: required 'key' parameter not set.");
	if (!$lg) $lg = $smarty->get_template_vars('lg');
	$text = db_Wording::getTranslation($key, $lg);
	if (!empty($printf_args)) $text = vsprintf($text, $printf_args);
    if ($escape) $text = htmlspecialchars($text);
    
	return $text;
}