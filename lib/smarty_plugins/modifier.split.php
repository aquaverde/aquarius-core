<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.split.php
 * Type:     modifier
 * Name:     split
 * Purpose:  split a string into an associative array
 *           
 * -------------------------------------------------------------
 */

function smarty_modifier_split($string, $delimiter = ',') {
	return explode($delimiter, $string);
}
