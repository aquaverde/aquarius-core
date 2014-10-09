<?php

class Formtype_Link extends Formtype {

    /** Apply formtype specific conversion prior to editing content
     */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        $valobject->formname = "field[".$formfield->name."][link][]";
        $valobject->formname2 = "field[".$formfield->name."][text][]";
        $valobject->formname3 = "field[".$formfield->name."][target][]";
        $valobject->formname4 = "field[".$formfield->name."][weight][]";

        $weight = 10;
        if(!is_array($valobject->value)) {
            // When the field was not initialized
            $valobject->value[] = array();
        } else {
            $values = $valobject->value;
            if($formfield->multi) {
                // Add a new empty link box
                $valobject->value[] = array();
            } else {
                $valobject->value = array();
                $valobject->value[0] = $values;
            }
            
            
            $i = 0;
            $valWeight = 10;
            foreach ($valobject->value as &$linkObject) {
                $linkObject['myindex'] = $i;
                $linkObject['weight'] = $valWeight;
                $i++;
                $valWeight += 10;
            }
        }
    }

    /** Apply formtype specific conversion prior to saving content
     */
    function post_contentedit($formtype, $field, $value, $node, $content) {
        $links = get($value, "link", null);
        $texts = get($value, "text", null);
        $targets = get($value, "target", null);
        $weights = get($value, "weight", null);
        
        $value = array();
        for($i = 0; $i < count($links); $i++) {
            if(!empty($links[$i])) {
                if(!isset($targets[$i])) $targets[$i] = "";
                if($weights[$i] == "") $weights[$i] = ($i+1)*10;
                $value[$weights[$i]] = array('link' => $links[$i], 'text' => $texts[$i], 'target' => $targets[$i]);
            }
        }
        ksort($value);
        $value = array_values($value);

        if(!$field->multi) {
            $value = array_pop($value);
        }

        return $value;
    }

    function db_get($value) {
        return $value;
    }
    
    function db_set($value) {
        return $value;
    }
}
