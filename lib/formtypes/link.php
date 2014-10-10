<?php
/** Store a link or a list of links
  *
  * Link data has the following properties:
  *   link: the actual Link data such as 'http://aquaverde.ch' which is placed in the href attribute.
  *   text: The text of the link. This is usually added inside the a-tag.
  *   target: The target attribute, see also config setting 'admin/link_target'
  *   weight: (it seems that this is used internally to sort multiple links)
  *
  * Form parameters:
  *   sup1: not used
  *   sup2: disables target selection when set to value 1
  *   sup3: override title of the link field
  *   sup4: override title of the target selection box
 */
class Formtype_Link extends Formtype {

    /** Apply formtype specific conversion prior to editing content
     */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        $valobject->formname = "field[".$formfield->name."][link][]";
        $valobject->formname2 = "field[".$formfield->name."][text][]";
        $valobject->formname3 = "field[".$formfield->name."][target][]";
        $valobject->formname4 = "field[".$formfield->name."][weight][]";

        // Prepare an empty link
        global $aquarius;
        $empty_link = array( 'target' => $aquarius->conf('admin/link_target') );
        
        if(!is_array($valobject->value)) {
            // When the field was not initialized
            $valobject->value[] = $empty_link;
        } else {
            $values = $valobject->value;
            if($formfield->multi) {
                // Add a new empty link box
                $valobject->value[] = $empty_link;
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
