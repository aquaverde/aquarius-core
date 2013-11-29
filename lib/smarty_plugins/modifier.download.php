<?php
/** Change relative filename URL into download link going through download.php */
function smarty_modifier_download($filepath) {
    $path = dirname($filepath);
    $file = basename($filepath);
    return "/download.php?path=".urlencode($path)."&amp;file=".urlencode($file);
}
