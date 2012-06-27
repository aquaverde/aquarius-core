<?php
// insert a gd redered title

function smarty_modifier_gd_title($string) 
{	
	return '<img src="/lib/gd/title.php?text='.urlencode(strtoupper($string)).'" alt="'.$string.'" />' ;	
}
?> 