<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.load_gmap_xml.php
 * Type:     function
 * Name:     load_gmap_xml
 * Purpose:  load an external xml for googlemaps  
 * -------------------------------------------------------------
 */
function smarty_function_load_gmap_xml($params, &$smarty) {
	// $file = get($params, 'file', 'Jura_Region.xml');
	// $url = FILEBASEDIR.'lib/XML/'.$file;
	
	$files = array(
				'http://www.tomas.ch/irs/exports/waadtland/Jura_Region.xml'
				// The following link is empty and I don't know why
                //, 'http://www.tomas.ch/irs/exports/waadtland/Pays3Lacs.xml'
				);	
	$markers = array();
	$replacer = array(chr(0xC2).chr(0x92) => "'", chr(13) => "", chr(96) => "'");
	
	foreach ($files as $url) {
		$xml = simplexml_load_file($url);

		foreach ($xml->accomodation as $marker) {
			// FIRST CHECK IF MARKER IS ALREADY IN ARRAY
			$already = false;
			foreach ($markers as $in_marker) {
				if($in_marker['lat'] == trim(str($marker->coords->latitude)) && $in_marker['lng'] == trim(str($marker->coords->longitute))) {
					$already = true;
					break;
				}
			}
			if($already) continue;
			// -----------------------------------------
			
			$marker_array = array();
			$marker_array['name'] = trim(strtr(str($marker->companyName1), $replacer));

			$city = "";
			$desc = "";
			foreach($marker->city->value as $my_value) {
				if((string)$my_value['language'] == $smarty->get_template_vars('lg')) $city = $my_value;
			}
			foreach($marker->description->value as $my_desc) {
				if((string)$my_desc['language'] == $smarty->get_template_vars('lg')) $desc = str($my_desc);
			}

			$marker_array['desc'] = 
				trim(strtr(str($marker->street), $replacer))."<br />".
				trim(str($marker->zipCode))." ".
				trim(strtr(str($city), $replacer))."<br/><br/>".
				trim(strtr($desc,$replacer));
			$marker_array['lat'] = trim(str($marker->coords->latitude));
			$marker_array['lng'] = trim(str($marker->coords->longitute));
			$marker_array['picture'] = trim(str($marker->pictureURL));
			$marker_array['link'] = trim(str($marker->tportalURL));
			$marker_array['cat'] = "external";

			$markers[] = $marker_array;
		}		
	}
	
	$smarty->assign("xml_markers",$markers);		
}
?>