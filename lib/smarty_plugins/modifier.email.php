<?php
/*  Format E-Mail with Javascript (anti spam war) 
    
    standard use:
    <p>{$email|email}</p>
    
    use with own linktext:
    {capture assign=linktext}{wording E-Mail Adress}{/capture}
    <p>{$email|email:$linktext}</p>
*/

function smarty_modifier_email($email, $linktext)
{
	list($name,$domainTLD)	= explode("@", $email);
	$domain	= substr($domainTLD, 0, strrpos($domainTLD, "."));
	$tld	= substr($domainTLD, strrpos($domainTLD, ".") + 1, strlen($domainTLD));
	
	return '<span class="shield" data-local="'.$name.'" data-domain="'.$domain.'" data-tld="'.$tld.'" data-linktext="'.$linktext.'"></span>';
}
