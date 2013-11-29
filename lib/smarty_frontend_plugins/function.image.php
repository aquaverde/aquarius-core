<?php 
/** image function.
  * Parameters:
  *     file: the file object in the respective language -> coming from a content, not a node
  *     th: use thumbnail, default false 
  *     alt: use alt, default false
  *     class: css class  
  *
  *		Example: {image file=$content->picture alt=TRUE}
  * 
*/

function smarty_function_image($params, &$smarty) 
{ 
	$thumb = $varparams = get($params, 'th', false) ;
	$alt = $varparams = get($params, 'alt', false) ;
	$file = $varparams = get($params, 'file') ;
	$width = $varparams = get($params, 'width', false) ;
	$class = $varparams = get($params, 'class') ;
    $filepath = $file['file'] ; 
    if ($thumb) { $filepath = smarty_modifier_th($file['file']) ; } 
    else if ($alt) { $filepath = smarty_modifier_alt($file['file']) ; }
	$legend = "" ; 
	if ($file['legend']) $legend = htmlspecialchars($file['legend']) ; 
	
	if ($class) $class = ' class="'.$class.'"' ; 
	
	if ($width) {
		return '<img src="'.$filepath.'" alt="'.$legend.'" title="'.$legend.'" width="'.$width.'"'.$class.' />' ;
	}
	else {
		return '<img src="'.$filepath.'" alt="'.$legend.'" title="'.$legend.'"'.$class.' />' ;
	}
}
?>