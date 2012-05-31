<?php
/** Handle file downloads with proper headers
  *
  * Request parameters:
  *   file: Filename requested for download
  *   path: Path where file resides
  *
  * Downloads are allowed from FILE_ROOT_DIRS only, excluding dot-files.
  */

$error = false;
$file = requestvar('file');
$path = requestvar('path');
$filepath = $path.'/'.$file;
$absolute_filepath = false;

if (!$error) {
    if (strlen($file) < 1) {
        $error = array('400', 'No file specified');
    }
    if (strlen($path) < 1) {
        $error = array('400', 'No path specified');
    }
}
if (!$error) {
    $absolute_filepath = realpath(FILEBASEDIR.$filepath);
    if (strpos($absolute_filepath, FILEBASEDIR) !== 0) {
        $error = array('403', 'Forbidden path');
        Log::info("Denied access to '$filepath': outside FILEBASEDIR");
    }
}
if (!$error) {
    if (preg_match('/\/\./', $absolute_filepath)) {
        $error = array('403', 'Forbidden path');
        Log::info("Denied access to '$filepath': it's a dot-file");
    }
}
if (!$error) {
    $allowed_dirs = explode(';', FILE_ROOT_DIRS);
    $first_dir = first(explode('/', substr($absolute_filepath, strlen(FILEBASEDIR))));
    if (!in_array($first_dir, $allowed_dirs)) {
        $error = array('403', 'Forbidden path');
        Log::info("Denied access to '$filepath': not in FILE_ROOT_DIRS");
    }
}
if (!$error) {
    if (!is_file($absolute_filepath)) {
        $error = array('404', 'Not found');
    }
}
if ($error) {
    list($code, $text) = $error;
    header($text, true, $code);
} else {
    $last_modified = filemtime($absolute_filepath);
    if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified) {
        header("HTTP/1.1 304 Not Modified");
    } else { 
        while(@ob_end_clean()); // close output buffers and ensure there's no stale output


        // Well, http://support.microsoft.com/kb/260519 says that Content-disposition: attachement; is enough to raise a "File Download" dialog. Not so for ASCII files.
        // Safari assumes text/plain when no Content-Type is specified (adhering to the MIME standard), this is not desired because it tries displaying the file as text even if it's a PDF
        // We want to force the download, so we supply a nonexisting content type so hopefully browsers do not try to display the file
        header("Content-Type: application/nonexisitingcontent-type");

        header("Content-Disposition: attachement; filename=".basename($absolute_filepath));
        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified)." GMT");
        header("Content-Length: ".filesize($absolute_filepath));
        readfile($absolute_filepath);
    }
}
flush_exit();
