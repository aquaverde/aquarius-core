<?php

/** Serve minified versions of the files listed within this block
  *
  * SCSS files are degenerated into CSS files and placed in the same path.
  *
  * Params:
  *   minify: Use the minifier, preset: true
  *   compress: Allow the minifier to use compression, preset: true
  */
function smarty_block_minify_includes($params, $content, $smarty, &$repeat) {
    if ($repeat) {
    } else {
        global $aquarius;
        $root = $aquarius->root_path;
        $debug = $aquarius->debug();
        $compress = (bool)get($params, 'compress', true);
        $minify = (bool)get($params, 'minify', true);

        $js_builder = new Minify_Link_Builder_JS($minify, $compress, $root, $debug);
        $css_builder = new Minify_Link_Builder_CSS($minify, $compress, $root, $debug);

        $lines = array_filter(array_map('trim', explode("\n", $content)));
        foreach($lines as $line) {
            if (substr($line, -5) == '.scss') {
                $css_builder->add($line);
            } elseif (substr($line, -4) == '.css') {
                $css_builder->add($line);
            } elseif (substr($line, -3) == '.js') {
                $js_builder->add($line);
            } else {
                throw new Exception("Minifying file '$line' not supported");
            }
        }

        return $css_builder->generate()
             . $js_builder->generate();
    }
}


class Minify_Link_Builder {
    var $files = array();

    function __construct($minify, $compress, $root_path, $debug) {
        $this->minify = (bool)$minify;
        $this->compress = (bool)$compress;
        $this->root_path  = $root_path;
        $this->debug = (bool)$debug;
    }

    function add($file) {
        $this->files []= $file;
    }

    private function urls() {
        $served_files = $this->served_files();
        if (count($served_files) < 1) {
            return array();
        }
        if ($this->minify) {
            $min_url = new Url('/min/');
            $min_url->add_param('f', join(',', $served_files));
            $min_url->add_param($this->max_mtime($this->root_path, $served_files));
            $min_url->add_param('compress', intval($this->compress));

            if ($this->debug) $min_url->add_param('debug', 1);

            return array($min_url);
        } else {
            return $served_files;
        }
    }

    function max_mtime() {
        $mtime = 0;
        foreach($this->files as $file) {
            $mtime = max($mtime, filemtime($this->root_path.$file));
        }
        return $mtime;
    }

    function generate() {
        $statements = '';
        foreach($this->urls() as $url) {
            $statements .= $this->build_statement($url);
        }

        return $statements;
    }

    function served_files() {
        return $this->files;
    }
}

class Minify_Link_Builder_JS extends Minify_Link_Builder {
    function build_statement($url) {
        return "<script src='$url' type='text/javascript'></script>";
    }
}

class Minify_Link_Builder_CSS extends Minify_Link_Builder {
    function build_statement($url) {
        return "<link href='$url' rel='stylesheet' type='text/css' />";
    }

    function served_files() {
        $served_files = array();
        foreach($this->files as $file) {
            if (substr($file, -5) == '.scss') {
                $served_files []= $this->degenerate_scss($file);
            } else {
                $served_files []= $file;
            }
        }
        return $served_files;
    }

    function degenerate_scss($scss_file) {
        $css_file = substr($scss_file, 0, -5).'.css';

        $scss_path = $this->root_path.$scss_file;
        $css_path = $this->root_path.$css_file;

        // Loop over all files in the dir to find the last date any SCSS was changed
        $scss_base = dirname($scss_path);
        $maxmtime = 0;
        $deps = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scss_base, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );
        foreach($deps as $path) {
            $extension = method_exists($path, 'getExtension') ? $path->getExtension() : array_pop(explode('.', $path->getFilename()));
            if (
                $path->isFile()
                && $extension == 'scss'
            ) {
                $maxmtime = max($maxmtime, $path->getMTime());
            }
        }

        if (filemtime($css_path) < $maxmtime) {
            $scss_compiler = new scssc();
            $scss_compiler->setImportPaths($scss_base);
            $source = file_get_contents($scss_path);
            if ($source === false) throw new Exception("Unable to read SCSS file $scss_path");
            $comp = "/* Generated ".date(DATE_W3C)." from $scss_file */\n".$scss_compiler->compile($source, $scss_file);
            $tmp_css_path = $css_path.'.'.uniqid();
            $result = file_put_contents($tmp_css_path, $comp);
            if ($result === false)  throw new Exception("Unable to write to $tmp_css_path after compiling $scss_file");
            touch($tmp_css_path, $maxmtime);
            if (!rename($tmp_css_path, $css_path)) throw new Exception("Unable to rename $tmp_css_path to $css_path after compiling $scss_file");
        }

        return $css_file;
    }
}
