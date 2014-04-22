<?php

function smarty_block_minify_includes($params, $content, $smarty, &$repeat) {
    if ($repeat) {
    } else {
        $js_files = array();
        $css_files = array();
        $scss_files = array();
        $lines = array_filter(array_map('trim', explode("\n", $content)));
        foreach($lines as $line) {
            if (substr($line, -5) == '.scss') {
                $scss_files []= $line;
            } elseif (substr($line, -4) == '.css') {
                $css_files []= $line;
            } elseif (substr($line, -3) == '.js') {
                $js_files []= $line;
            } else {
                throw new Exception("Minifying file '$line' not supported");
            }
        }

        global $aquarius;
        $root = $aquarius->root_path;

        // Degenerate SCSS into CSS
        if ($scss_files) {
            $scss_compiler = new scssc();

            foreach($scss_files as $scss_file) {
                $css_file = substr($scss_file, 0, -5).'.css';
                $css_files []= $css_file;

                $scss_path = $root.$scss_file;
                $css_path = $root.$css_file;

                if (filemtime($css_path) < filemtime($scss_path)) {
                    $scss_compiler->setImportPaths(dirname($scss_path));
                    $source = file_get_contents($scss_path);
                    if ($source === false) throw new Exception("Unable to read SCSS file $scss_path");
                    $comp = "/* Generated ".date(DATE_W3C)." from $scss_file */\n".$scss_compiler->compile($source);
                    $tmp_css_path = $css_path.'.'.uniqid();
                    $result = file_put_contents($tmp_css_path, $comp);
                    if ($result === false)  throw new Exception("Unable to write to $tmp_css_path after compiling $scss_file");
                    touch($tmp_css_path, filemtime($scss_path));
                    if (!rename($tmp_css_path, $css_path)) throw new Exception("Unable to rename $tmp_css_path to $css_path after compiling $scss_file");
                }
            }
        }

        // Replace content with proper links
        if ($css_files) {
            $css_min_url = new Url('/min/');
            $css_min_url->add_param('f', join(',', $css_files));
            $css_min_url->add_param(smarty_block_minify_includes_max_mtime($root, $css_files));
            global $aquarius;
            if ($aquarius->debug()) $css_min_url->add_param('debug', 1);
            $links .= "<link href='$css_min_url' rel='stylesheet' type='text/css' />";
        }
        if ($js_files) {
            $js_min_url = new Url('/min/');
            $js_min_url->add_param('f', join(',', $js_files));
            $js_min_url->add_param(smarty_block_minify_includes_max_mtime($root, $js_files));
            global $aquarius;
            if ($aquarius->debug()) $js_min_url->add_param('debug', 1);
            $links .= "<script src='$js_min_url' type='text/javascript'></script>";
        }
        return $links;
    }
}

function smarty_block_minify_includes_max_mtime($root, $files) {
    $mtime = 0;
    foreach($files as $file) {
        $mtime = max($mtime, filemtime($root.$file));
    }
    return $mtime;
}
