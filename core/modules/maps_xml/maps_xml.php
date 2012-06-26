<?
class Maps_Xml extends Module {
	var $register_hooks = array('daily');
    
    var $short = "maps_xml";
    var $name  = "Load extern maps-xml";

	function daily() {
		$files = array(
					'http://www.tomas.ch/irs/exports/waadtland/Jura_Region.xml', 
					'http://www.tomas.ch/irs/exports/waadtland/Pays3Lacs.xml'
					);	
		$markers = array();
		$replacer = array(chr(0xC2).chr(0x92) => "'", chr(13) => "", chr(96) => "'");

		$langs = db_Languages::getLanguages(true);
		
		foreach ($files as $url) {
			$xml = simplexml_load_file($url);
			
			foreach ($langs as $lang) {
				foreach ($xml->accomodation as $marker) {				
					// FIRST CHECK IF MARKER IS ALREADY IN ARRAY
					$already = false;
					if(isset($markers[$lang->lg]))
					{
						foreach ($markers[$lang->lg] as $in_marker) {
							if($in_marker['lat'] == trim(str($marker->coords->latitude)) && $in_marker['lng'] == trim(str($marker->coords->longitute))) {
								$already = true;
								break;
							}
						}
					}
					if($already) continue;
					// -----------------------------------------

					$marker_array = array();
					$marker_array['name'] = trim(strtr(str($marker->companyName1), $replacer));

					$city = "";
					$desc = "";
					foreach($marker->city->value as $my_value) {
						if((string)$my_value['language'] == $lang->lg) $city = $my_value;
					}
					foreach($marker->description->value as $my_desc) {
						if((string)$my_desc['language'] == $lang->lg) $desc = str($my_desc);
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

					$markers[$lang->lg][] = $marker_array;
				}
			}		
		}

		foreach ($langs as $lang) {
			$json = json_encode($markers[$lang->lg]);
			$filename = FILEBASEDIR."cache/maps_extern_".$lang->lg.".json";

			if (!$handle = fopen($filename, "w")) {
				Log::warn("Datei ".$filename." (extern-xml für maps) konnte nicht angelegt/bearbeitet werden");
			}

		    if (!fwrite($handle, $json)) {
				Log::warn("Kann nicht in die Datei ".$filename." (extern-xml für maps) schreiben");
		    }

		    fclose($handle);
		}
	}
}
?>