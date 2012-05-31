<?php
// insert a gd redered title

function smarty_modifier_gd_navig($string, $transparency, $upper) 
{	
	
	if ($transparency == 1) $transparency = '&amp;transparency=1' ;
	if ($upper == 1) $string = strtoupper($string) ;
	
	return '/lib/gd/render_navig.php?text='.urlencode($string).$transparency ;
	
}
?> 