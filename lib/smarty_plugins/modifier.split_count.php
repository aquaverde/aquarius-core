<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.split_count.php
 * Type:     modifier
 * Name:     split_count
 * Purpose:  counts the elements of a string that is delimited
 *
 *           
 * -------------------------------------------------------------
 */

function smarty_modifier_split_count($string, $delimiter = ',')
{	
	if (empty($string)) return 0; // empty strings have no elements
	else                return count(explode($delimiter,$string));
}
