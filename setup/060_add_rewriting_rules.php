<?php

$template_contents = file_get_contents($aquarius->core_path.'htaccess.webroot');

$htf = $aquarius->root_path.'.htaccess';

$contents = '';
if (file_exists($htf)) {
    $contents = file_get_contents($htf);
}

$replace_contents = $contents;
if (preg_match('/# Aquarius rules/', $contents)) {
    $message = 'Replaced rewriting rules in .htaccess in webroot';
    $replace_contents = preg_replace('/# Aquarius rules AUTOREPLACE.*# End of Aquarius rules\s?/s', $template_contents, $contents);
} else {
    $message = 'Added Aquarius rewriting rules to .htaccess-file in webroot';
    $replace_contents = $template_contents;
}

if ($replace_contents !== $contents) {
    $success = file_put_contents($htf, $replace_contents);
    if ($success === false) {
        message('warn', 'Unable to update .htaccess file in webroot, please copy manually');
    } else {
        message('', $message);
    }
}
