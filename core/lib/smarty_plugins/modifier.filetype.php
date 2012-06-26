<?php
/*
 * get file type
 */

function smarty_modifier_filetype($string)
{	
		
	$file = '.'.$string ; 
	$retStr = "" ; 
	
	if (file_exists($file)) 
	{
    	$infos = pathinfo ($file) ;
        $extension = strtoupper($infos['extension']) ;		
	}
	
	return $extension ; 
}
?> 