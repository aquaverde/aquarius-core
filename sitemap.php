<?
/** Provide a sitemap.xml depending on host */
$host = $_SERVER['HTTP_HOST'];
$name = "aquarius/cache/sitemap-".basename(strtolower($host)).'.xml';
if (file_exists($name)) {
    header('Content-type: text/xml');
    readfile($name);
} else {
    header("HTTP/1.1 404 No sitemap for host '$host'");
}
?>
