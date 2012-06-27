<?php
/*
 * aquafy text
 */

function smarty_modifier_aquafy($string)
{	
	$string = nl2br($string) ;

	// clear <br /> and <p> for a clean listing
	$string = str_replace("</h3><br />","</h3>", $string);
	$string = str_replace("</h2><br />","</h2>", $string);
	$string = str_replace("</li><br />","</li>", $string);
	$string = str_replace("<ul><br />","<ul>", $string);
	$string = str_replace("</ul><br />","</ul>", $string);
	$string = str_replace("<ol><br />","<ol>", $string);
	$string = str_replace("</ol><br />","</ol>", $string);	
	$string = str_replace("<ul>","</p>\n<ul>", $string);
	$string = str_replace("</ul>","</ul>\n<p>", $string);
	$string = str_replace("<ol>","</p>\n<ol>", $string);
	$string = str_replace("</ol>","</ol>\n<p>", $string);

  	$string = htmlspecialchars ($string) ; 
  
  	// exeptions for tags, links
  	$string = str_replace("&lt;","<", $string);
  	$string = str_replace("&gt;",">", $string);
  	$string = str_replace("href=&quot;","href=\"", $string);
  	$string = str_replace("&quot;>","\">", $string);
  	$string = str_replace("’","&rsquo;", $string);
  	$string = str_replace("…","&hellip;", $string);
  	$string = str_replace("„","&bdquo;", $string);
  	$string = str_replace("–","&ndash;", $string);
  	$string = str_replace("“","&ldquo;", $string);
	
	return $string;
}
?> 