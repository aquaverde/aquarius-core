<?php

function smarty_block_minify_includes($params, $content, $smarty, &$repeat) {
    if ($repeat) {
    } else {
        $js_files = array();
        $css_files = array();
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        foreach($lines as $line) {
            if (substr($line, -4) == '.css') {
                $css_files []= $line;
            } elseif (substr($line, -3) == '.js') {
                $js_files []= $line;
            } else {
                throw new Exception("Minifying file '$line' not supported");
            }
        }
        
        // Replace content with proper links
        if ($css_files) {
            $css_min_url = new Url('/min/');
            $css_min_url->add_param('f', join(',', $css_files));
            $css_min_url->add_param(smarty_block_minify_includes_max_mtime($css_files));
            global $aquarius;
            if ($aquarius->debug()) $css_min_url->add_param('debug', 1);
            $links .= "<link href='$css_min_url' rel='stylesheet' type='text/css'>";
        }       
        if ($js_files) {
            $js_min_url = new Url('/min/');
            $js_min_url->add_param('f', join(',', $js_files));
            $js_min_url->add_param(smarty_block_minify_includes_max_mtime($js_files));
            global $aquarius;
            if ($aquarius->debug()) $js_min_url->add_param('debug', 1);
            $links .= "<script src='$js_min_url' type='text/javascript'>";
        }
        return $links;
    }
}

function smarty_block_minify_includes_max_mtime($files) {
    global $aquarius;
    $root = $aquarius->root_path;
    $mtime = 0;
    foreach($files as $file) {
        $mtime = max($mtime, filemtime($root.$file));
    }
    return $mtime;
}