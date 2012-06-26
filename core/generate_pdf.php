<?php
    
    $node = get($_REQUEST, 'node_id');
	if (!$node) $smarty->trigger_error("Node_id is missing for generating pdf") ;
    
    $lg = get($_REQUEST, 'lg');
	if (!$lg) $smarty->trigger_error("Language is missing for generating pdf") ;
	
	$template = basename(get($_REQUEST, 'template', PDF_STANDART_TEMPLATE));
	
	$prefix = get($_REQUEST, 'prefix', PDF_STANDART_PREFIX);

    $node = db_Node::get_node($node);
    if (!$node) $smarty->trigger_error("Node is missing for generating pdf") ;
    
    $content = $node->get_content($lg);
    $smarty = $aquarius->get_smarty_frontend_container($lg, $node);
    
	require_once("lib/dompdf/dompdf_config.inc.php");
    
    $smarty->assign('entry', $content);
    
    // GET THE HTML
    $myhtml = $smarty->fetch($template);

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
