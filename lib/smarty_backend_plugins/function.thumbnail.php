<?php
/** Include a thumbnail for a file
  * Params:
  *     file (Path, string): Filepath relative to public dir or fileinfo object
  *     link (Boolean): Wrap the thumb in a link to the file. For images, a popup opens
  *     class (Class name, string): Class name for the <img>, defaults to 'thumb'
  *     show_filename (boolean): Add filename for files that are not pictures, default false
  *     width, height (in pixels, optional): passed to resize function if set 
  */
function smarty_function_thumbnail($params, $smarty) {
    $smarty->loadPlugin('smarty_modifier_truncate');
    extract(validate_or_die($params, array(
        'file'          => 'object string notset',
        'link'          => 'bool notset',
        'class'         => 'string notset',
        'show_filename' => 'bool notset'
    )));

    if (!is_object($file)) $file = FileInfo::public_file($file);

    // If there is no file, we output just a space.
    if (!$file || !is_file($file->filepath)) return '&nbsp';

    if (empty($class)) $class = 'thumb';

    $filename = $file->name();
    $pathinfo = pathinfo($filename);
    $extension = strtolower($pathinfo['extension']);
    switch ($extension) {
        case 'jpg':
        case 'jpeg':
        case 'gif':
        case 'png':
            $resize_options = array('image' => '/'.$file->publicpath(), 'th' => true, 'crop' => false);
            if ($width = get($params, 'width'))   $resize_options['width'] = $width;
            if ($height = get($params, 'height')) $resize_options['height'] = $height;
            $thumb_path = smarty_function_resize($resize_options, $smarty);
            $onclick = false;
            $title = $filename;
            if ($link) {
                $image_dimensions = getimagesize($file->filepath);
                $popup_width = $image_dimensions[0] + 20;
                $popup_height = $image_dimensions[1] + 20;
                $file_href = $file->href();
                $title = new Translation('s_preview');
                $onclick = "onclick=\"openBrWindow('$file_href','', 'height=$popup_height,width=$popup_width,top=100,left=200,toolbar=no,status=yes,resizable=yes,menubar=no,scrollbars=yes')\"";
            }
            return "<img src='$thumb_path' alt='$filename' title='$title' class='$class' $onclick />";
        case 'swf':
            $href = $file->href();
            $str = "
                <object type='application/x-shockwave-flash' data='$href' width='100' height='50'>
                    <param name='movie' value='$href' />
                </object>";
            if ($link) return "<a href='$href' target='_blank'>$str</a>";
            else return $str;
        default:
            $href = $file->href();
            $button = getFileButton($file->name());
            $escaped_name = htmlspecialchars($filename);
            $str = "<img src='buttons/$button' vspace='3' hspace='5' border='0' alt='$escaped_name' title='$escaped_name'/>";
            if ($show_filename) $str .= "<div title='$escaped_name'>".htmlspecialchars(smarty_modifier_truncate($filename, 20, '…', true, true)).'</div>';
            if ($link) return "<a href='$href' target='_blank'>$str</a>";
            else return $str;
    }
}
