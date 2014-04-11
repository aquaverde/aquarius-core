<?php
/* The maps module allows placing points and ways on a map.
 *
 * This module is a snowflake, a fractal of special!
 */
class Formtype_googlemaps extends Formtype {
    
    var $presets;
    
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        // Lift to array
        if(!$formfield->multi) {
            $valobject->value = array_filter(array($valobject->value)); // lift
        }
        foreach($valobject->value as &$mark) {
            $mark['kat'] = get($mark, 'kat'); // Avoid warnings
            $mark['link'] = get($mark, 'link'); // Avoid warnings
        }
        
        // Read preset lat,lon,zoom from sup3 or use default config values
        $form_presets = explode(',', $formfield->sup3);
        $our_presets = clone $this->presets;

        foreach(array('lat', 'lon', 'zoom') as $var) {
            $form_preset = array_shift($form_presets);
            if (is_numeric($form_preset)) $our_presets->position[$var] = floatval($form_preset);
        }
        
        $marker_types = $this->presets->marker_types($content->lg);
        $icon_types = array();
        foreach($marker_types as $marker) { 
            $icon_types[$marker['id']] = $marker['icon'];
        }
        $map_options = array(
            'data'      => $valobject->value,
            'multi'     => $formfield->multi,
            'presets'   => $our_presets,
            'htmlid'    => $valobject->htmlid,
            'formname'  => $valobject->formname,
            'marker_types' => $marker_types,
            'icon_types'   => $icon_types,
        );
        
        /* magic field name */
        if (!empty($content->kml_file)) {
            $map_options['kml_file'] = PROJECT_URL.$content->kml_file.file;
        }
        
        $valobject->map_options = $map_options;
        $valobject->marker_types = $marker_types;
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
