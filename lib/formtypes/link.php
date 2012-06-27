<?
class Formtype_Link extends Formtype {

    /** Apply formtype specific conversion prior to editing content
     */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        $valobject->formname = "field[".$formfield->name."][link]";
        $valobject->formname2 = "field[".$formfield->name."][text]";
        $valobject->formname3 = "field[".$formfield->name."][target]";
        $valobject->formname4 = "field[".$formfield->name."][weight]";
        $valobject->links = array();
        $valobject->texts = array();
        $valobject->targets = array();
        $valobject->weights = array();
        $weight = 10;
        if(!is_array($valobject->value)) {
            $valobject->links[] = $valobject->value;
            $valobject->texts = "";
            $valobject->targets = "";
            $valobject->linkformcount = 1;
            $valobject->weights[] = $weight;
            
            $valobject->value[] = array();
        } else {
            $values = $valobject->value;
            if($formfield->multi) {
                $valobject->formname .= "[]";
                $valobject->formname2 .= "[]";
                $valobject->formname3 .= "[]";
                $valobject->formname4 .= "[]";
                
                $valobject->value[] = array();
            } else {
                //$values = array($values);
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

            foreach($values as $linkvalue) {
                $valobject->links[] = get($linkvalue, 'link', '');
                $valobject->texts[] = get($linkvalue, 'text', '');
                $valobject->targets[] = get($linkvalue, 'target', '');
                $valobject->weights[] = $weight;
                $weight += 10;
            }
            
            $valobject->linkformcount = count($valobject->links);
            $valobject->lastweight = $weight;
        }
    }

    /** Apply formtype specific conversion prior to saving content
     */
    function post_contentedit($formtype, $field, $value, $node, $content) {
        $links = get($value, "link", null);
        $texts = get($value, "text", null);
        $targets = get($value, "target", null);
        $weights = get($value, "weight", null);
        if($field->multi) {
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
        } else {
            $value = array('link' => $links, 'text' => $texts, 'target' => $targets);
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
?>