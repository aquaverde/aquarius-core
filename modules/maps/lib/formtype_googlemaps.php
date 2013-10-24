<?php

class Formtype_googlemaps extends Formtype {
    
    var $presets;
    
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        // Lift to array
        if(!$formfield->multi) {
            $valobject->value = array($valobject->value); // lift
        }
        
        // Read preset lat,lon,zoom from sup3 or use default config values
        $form_presets = explode(',', $formfield->sup3);
        foreach(array('lat', 'lon', 'zoom') as $var) {
            $form_preset = array_shift($form_presets);
            $valobject->$var = is_numeric($form_preset) ? $form_preset : $this->presets->position[$var];
        }
        
        $valobject->presets = $form_presets;
        $valobject->marker_types = $this->presets->marker_types($content->lg);
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
