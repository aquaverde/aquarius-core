<?php
/*
 * size, in kb or mb. returns e.g. "309 KB", "1.13 MB"
 */

function smarty_modifier_size($string)
{	
	$file = '.'.$string ; 
	$retStr = "" ; 
	
	if (file_exists($file)) 
	{
		$numBytes = intval(filesize($file) * 0.001) ; 
		if ($numBytes >= 1024)
		{
			$retStr = sprintf("%.2f&nbsp;MB", $numBytes / 1024) ; 
		}
		else $retStr = $numBytes.'&nbsp;KB' ;
	}
	
	return $retStr ; 
}
?> 