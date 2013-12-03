<?php 
/** Single date field
  * Stored as UNIX epoch timestamp (seconds since 1.1.1970) */
class Formtype_Date extends Formtype {

    function to_string($values) {
        return strftime(DATE_FORMAT, $values);
    }

    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        if($formfield->multi) 
        {
            $valobject->formname .= "[]";
            
            // Add an empty field at the end
            $valobject->value[] = array();
            
            $i = 0;
            foreach ($valobject->value as &$dateObject) {
                $mydate = $dateObject;
                $dateObject = array();
                $dateObject['myindex'] = $i;
                if($mydate) $dateObject['date'] = strftime(DATE_FORMAT, $mydate);
                $i++;
            }
        } 
        else 
        {
            if(!empty($valobject->value)) {
                $mydate = strftime(DATE_FORMAT, $valobject->value);
                $valobject->value = array();
                $valobject->value[0] = array('date' => $mydate);
            }
            else
            {
                $valobject->value = array();
                $valobject->value[0] = array();
            }
        }
        
    }

    function post_contentedit($formtype, $field, $value, $node, $content) { 
        
        $dates = $value;
        
        if($field->multi) {
            $value = array();
            for($i = 0; $i < count($dates); $i++) {
                if(!empty($dates[$i])) {
                    $value[$dates[$i]] = parse_date($dates[$i]);
                }
            }
            ksort($value);
            $value = array_values($value);
        } else {
            $value = parse_date($dates);
        }    
        
        return $value;
    }

    function db_get($values) {
        return intval(first($values)); // Unix epoch
    }

    function db_set($values) {
        return array(intval($values));
    }
}
