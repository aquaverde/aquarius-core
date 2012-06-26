<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     truncate_at.split.php
 * Type:     modifier
 * Name:     truncate_at
 * Purpose:  truncate a strin at a given length and add ...
 *           
 * -------------------------------------------------------------
 */

function smarty_modifier_truncate_at($string, $at) {
	if (strlen($string) <= $at) return $at ; 
	else return substr($string, 0, $at)."..." ; 
}
?> 