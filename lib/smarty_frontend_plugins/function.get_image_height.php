<?php 
/** image get height function.
  * Parameters:
  *     file: the file object in the respective language -> coming from a content, not a node
  * 
*/

function smarty_function_get_image_height($params, &$smarty) 
{ 
	$file = $varparams = get($params, 'file', NULL) ;
	if (!$file) return 0 ; 

	// $dirname = dirname($file['file']) ; 
	// $filename = basename($file['file']) ; 
	$filepath = $file['file'] ; 
	
	list($width, $height, $type, $attr) = getimagesize(FILEBASEDIR.$filepath) ;
	return $height ; 
}
?>