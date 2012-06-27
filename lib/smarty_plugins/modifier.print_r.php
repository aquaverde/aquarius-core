<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.print_r.php
 * Type:     modifier
 * Name:     print_r
 *           
 * -------------------------------------------------------------
 */

function smarty_modifier_print_r($var)
{	
	$output = '<div class="debug"><pre>';
	$output .= print_r($var, true);
	$output .= "</pre></div>";
	
	return $output;
}
?> 