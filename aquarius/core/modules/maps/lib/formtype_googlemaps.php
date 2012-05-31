<?php

class Formtype_googlemaps extends Formtype {
	
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
		if(!$formfield->multi) {			
			$valobject->value = array($valobject->value);			
		}
		
		// Read preset lat,lon,zoom from sup3 or use default config values
		$form_presets = explode(',', $formfield->sup3);
		foreach(array(
            'lat'  => MAP_DEFAULT_LAT,
            'lon'  => MAP_DEFAULT_LNG,
            'zoom' => MAP_DEFAULT_ZOOM,
		) as $var => $default) {
            $form_preset = array_shift($form_presets);
            $valobject->$var = is_numeric($form_preset) ? $form_preset : $default;
        }
	}
	
	function post_contentedit($formtype, $field, $value, $node, $content) {
		if(!is_array($value)) $value = array();
				
		if(!$field->multi) {
			$value = first($value);			
		}		
		return $value;
	}
	
    function db_get($vals) {
        return $vals;
    }

    function db_set($vals) {
        return $vals;
    }
    
}

?>