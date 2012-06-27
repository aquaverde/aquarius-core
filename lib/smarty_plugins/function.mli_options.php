<?
/** Function that generates an option list for the multi line input from a string

*/
function smarty_function_mli_options($params, &$smarty) {
    
    $value = get($params, 'value');
	
    if (!$value) $smarty->trigger_error("mli_options: required 'value' parameter not set.");

	$options	= split(";", $value);
	
	foreach ( $options as $opt )
		$html_code .= "<option value=\"$opt\">$opt\n";

	return $html_code;
}
?>