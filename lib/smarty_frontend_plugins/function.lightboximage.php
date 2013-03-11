<?php 
/** lightboximage function.
  * Parameters:
  *     file: the file object in the respective language -> coming from a content, not a node
  *		key: lightbox key
  *     th: use thumbnail, default false 
  *     alt: use alt, default false
  *
  *		Example: {lightboximage file=$content->picture alt=true key="content"}
  * 
*/

function smarty_function_lightboximage($params, &$smarty) 
{ 
	$thumb = get($params, 'th', false) ;
	$alt = get($params, 'alt', false) ;
	$file = get($params, 'file', false) ;
	$width = get($params, 'width', false) ;
	$key = get($params, 'key', "") ;
	
	if (!$file || $key == "") $smarty->trigger_error("lightboximage: no file or no key specified. Errorf");
	
	$dirname = dirname($file['file']) ; 
	$filename = basename($file['file']) ; 
	$filepath = $file['file'] ; 
	if ($thumb) { $filepath = $dirname.'/th_'.$filename ; } 
	else if ($alt) { $filepath = $dirname.'/alt_'.$filename ; }
	
	$html = "" ; 
	$html .= '<a href="'.$file['file'].'" rel="lightbox['.$key.']" title="'.$file['legend'].'">' ; 			
	
	$legend = "" ; 
	if ($file['legend']) $legend = $file['legend'] ; 
	if ($width) {
		$html .= '<img src="'.$filepath.'" alt="'.$legend.'" title="'.$legend.'" width="'.$width.'" />' ; 
	}
	else {
		$html .= '<img src="'.$filepath.'" alt="'.$legend.'" title="'.$legend.'" />' ; 
	}
	
	$html .= '</a>' ; 
	
	return $html ; 
	
}
?>