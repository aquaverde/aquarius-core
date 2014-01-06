<?php

/*
NOTE needs the following in htaccess:
RewriteRule ^pdfs/(..)/(\d+)/.* /aquarius/core/frontend/generate_pdf.php?lg=$1&node_id=$2 [qsappend,last]
*/

require '../lib/init.php';

if (!$aquarius->conf('pdfgen/enabled')) {
    throw new Exception("Generating PDF not enabled");
}

$nodestr = get($_REQUEST, 'node_id');
if (!$nodestr) throw new Exception("node_id missing");

$lg = get($_REQUEST, 'lg');
if (!$lg) throw new Exception("lg missing");

$lg = db_Languages::validate_code($lg);
if (!$lg) throw new Exception("language invalid");


$node = db_Node::get_node($nodestr);
if (!$node) throw new AquaException(array("Not available", "Couldn't load '$nodestr'"));
if (!$node->active()) throw new AquaException(array("Not available", "Node ".$node->idstr()." is not active."));
if ($node->access_restricted_node()) throw new AquaException(array("Not available", "Node ".$node->idstr()." is access restricted"));
    

$template = basename(get($_REQUEST, 'template', $aquarius->conf('pdfgen/standard_template')));

$prefix = get($_REQUEST, 'prefix', $aquarius->conf('pdfgen/prefix'));
    
$content = $node->get_content($lg);
if (!$content) throw new AquaException(array("Not available", "Node ".$node->idstr()." has no content in language $lg"));
if (!$content->active()) throw new AquaException(array("Not available", "Content for ".$node->idstr()." is not active in language $lg"));

$smarty = $aquarius->get_smarty_frontend_container($lg, $node);
    
    require_once("lib/dompdf/dompdf_config.inc.php");
    
    $smarty->assign('entry', $content);
    
    // GET THE HTML
    $cacheid = $node->id.'.'.$lg;
    $myhtml = $smarty->fetch($template, $cacheid);

    // CREATE THE PDF
    $dompdf = new DOMPDF();
    $dompdf->set_base_path(FILEBASEDIR);
    $dompdf->load_html($myhtml);
    $dompdf->set_paper('A4', 'portrait');
    
    $dompdf->render();
    $pdf_string = $dompdf->output();

    // sanitize filename to only contain letters, numbers, dashes and dots, everything else is replaced by underscores
    $filename = strtolower($prefix.'-'.$content->get_title().'.pdf');
    $filename = preg_replace("%[^\p{L}\p{N}.-]+%u", "_", $filename);
    
    // find byte size. Must use mb_strlen() with one-byte encoding like latin1 so that it doesn't try reading multibyte characters in case strlen() was replaced by mb_strlen
    $fsize = mb_strlen($pdf_string, 'latin1');

    header("Content-type: application/pdf"); // add here more headers for diff. extensions
    header("Content-Disposition: attachment; filename=\"$filename\""); // use 'attachment' to force a download
    header("Content-length: $fsize");
    header("Cache-control: private"); //use this to open files directly
    
    while(@ob_end_clean()); // Nuke output buffer
    
    print $pdf_string;
    exit;
