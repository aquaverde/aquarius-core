<?php
/*
    define("SIZE", "70");
    define("MAXLENGTH", "160");
    define("ROWS", "10");
    define("COLS", "70");
*/
    // size definitions for MLE's:

    $s_type = array("s" => "small",
                    "m" => "medium",
                    "l" => "large");

    $s_size = array("s" => array(3,70),
                    "m" => array(10,70),
                    "l" => array(30,70));

    $h_ef = array("value" => "ef",
                  "title" => "Entryfield");

    $h_mle = array("value" => "mle",
                   "title" => "Multiline-entryfield");

    $h_date = array("value" => "date",
                   "title" => "Date");

    $h_fl = array("value" => "file",
                  "title" => "File");
				  
	$h_sf = array("value" => "sf",
				  "title" => "Selectfield");
				  
    $h_cb = array("value" => "radiobool",
                  "title" => "Radiobool");
				  
	$h_chk = array("value" => "checkbox",
                  "title" => "Checkbox");
    
   	$h_rte = array("value" => "rte",
                  "title" => "Richtext");

    $ml_in	= array("value" => "mli",
					"title" => "Multiple input");
	
	$point	= array("value" => "point",
					"title" => "Pointing");
					
    $f_type = array($h_ef, $h_mle, $h_date, $h_sf, $h_fl, $h_chk, $h_cb, $h_rte, $ml_in, $point);


?>
