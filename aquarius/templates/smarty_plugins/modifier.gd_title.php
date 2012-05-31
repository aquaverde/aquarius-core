<?php
// insert a gd redered title

function smarty_modifier_gd_title($string,$width=0) 
{	
	$widthparam="";
	if ($width)$widthparam="&amp;width=$width";
	return '<img src="/lib/gd/title.php?text='.urlencode($string).$widthparam.'" alt="'.$string.'" class="slogan" />' ;	
}
?> 