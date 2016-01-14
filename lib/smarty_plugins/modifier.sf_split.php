<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.sf_split.php
 * Type:     modifier
 * Name:     sf_split
 * Purpose:  split a string like xx=yyyy,aa=eeee into an 
 *			 associative array
 *           
 * -------------------------------------------------------------
 */

function smarty_modifier_sf_split($string, $delimiter1 = ',', $delimiter2 = '=') {
    $result    = array();
    $baseArray = explode($delimiter1,$string);

    foreach ( $baseArray as $base ) {
        // split up value and text
        $parts = explode($delimiter2, $base);
        $result[$parts[0]] = get($parts, 1, $parts[0]);
    }

    return $result;
}
