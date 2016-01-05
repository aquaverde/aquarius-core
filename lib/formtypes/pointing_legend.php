<?php 

class Formtype_Pointing_Legend extends Formtype {

    function pre_contentedit($node, $content, $formtype, $formfield, $valobject, $page_requisites) {
        
        if(!$formfield->multi) throw new Exception("Formfield Pointing-Legend have to be multi");
        
        $rootid = $formfield->sup2;
        if (empty($rootid)) {
            $rootnode = db_Node::get_root();
        } else {
            $rootnode = db_Node::get_node($rootid);
            if (!$rootnode) throw new Exception("Invalid pointing root node '$rootid' in formfield $formfield->id (sup2)");
        }

        // Depth of selection tree
        $depth = intval($formfield->sup3);
        if ($depth < 1) $depth = false;
        
        $valobject->disabled_depths = array();
        if($formfield->sup4 != "") {
            $valobject->disabled_depths  = split(",", $formfield->sup4);
        }
        $valobject->disabled_depths []= 0; // Root may never be selected

        $valobject->formname = "field[".$formfield->name."][node][]";
        $valobject->formname2 = "field[".$formfield->name."][legend][]";
        $valobject->formname3 = "field[".$formfield->name."][weight][]";
        
        $valobject->pointings = array();
        $valobject->legends = array();
        $valobject->weights = array();
        $weight = 10;

        $valobject->value[] = array();
        $values = $valobject->value;
        
        $i = 0;
        $valWeight = 10;
        foreach ($valobject->value as &$poiObject) {
            $poiObject['myindex'] = $i;
            $poiObject['weight'] = $valWeight;
            $poiObject['popupid'] = $valobject->htmlid . "_" . $i;
            $i++;
            $valWeight += 10;
        }

        foreach($values as $poivalue) {
            $valobject->pointings[] = get($poivalue, 'node', '');
            $valobject->legends[] = get($poivalue, 'legend', '');
            $valobject->weights[] = $weight;
            $weight += 10;
        }
        
        $valobject->poiformcount = count($valobject->pointings);
        $valobject->lastweight = $weight;

        $valobject->popup_action = Action::build(
            array(
                'nodes_select'
                , 'tree'
                , false
                , $content->lg
                , $rootnode->id
                , $depth, $formfield->sup4
                , false
            )
        );

        $valobject->row_action = Action::make('pointing_legend_ajax', 'empty_row');
    }

    function post_contentedit($formtype, $field, $value, $node, $content) {        
        $nodes = get($value, "node", null);
        $legends = get($value, "legend", null);
        $weights = get($value, "weight", null);

        $value = array();
        for($i = 0; $i < count($nodes); $i++) {
            if(!empty($nodes[$i])) {
                
                if(!isset($legends[$i])) $legends[$i] = "";
                
                if($weights[$i] == "") $weights[$i] = ($i+1)*10;
                
                $value[$weights[$i]] = array('node' => db_Node::get_node($nodes[$i]), 'legend' => $legends[$i]);
            }
        }
        ksort($value);
        $value = array_values($value);
        
        return $value;
    }

    /** Load node object from id */
    function db_get($values, $formfield, $lg) {
        if (!empty($values['node'])) {
            $pointing_node = db_Node::staticGet($values['node']);
            if ($pointing_node) {
                $values['node'] = $pointing_node;
                return $values;
            }
        }
        return null;
    }

    /** Save node id to DB. */
    function db_set($value, $formfield, $lg) {
        $node = db_Node::get_Node($value['node']); // Make sure it is a node object
        if ($node) {
            $value['node'] = $node->id;
            return $value;
        }
        return null;
    }
}
