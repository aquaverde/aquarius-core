<?php
/** Format E-Mail with Javascript (anti spam war) */

function smarty_modifier_email($email)
{
	list($name,$domainTLD)	= split("@", $email);
	$domain	= substr($domainTLD, 0, strrpos($domainTLD, "."));
	$tld	= substr($domainTLD, strrpos($domainTLD, ".") + 1, strlen($domainTLD));
	
	return '<script type="text/javascript">email(\''.$name.'\', \''.$domain.'\', \''.$tld.'\');</script>';
}
?>
