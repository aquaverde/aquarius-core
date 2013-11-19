<?php

class Maps_Presets {
    var $api_key;
    var $markers_in = 'gmapc';
    var $default_marker = array(
            'value' => false,
            'id' => false,
            'name' => 'default',
            'selection_name' => 'default',
            'icon' => 'default.png'
    );
    
    function __construct($api_key, $position, $polyline, $markers_in) {
        $this->api_key = $api_key;
        $this->position = $position;
        $this->polyline = $polyline;
        $this->markers_in = $markers_in;
    }
    
    function marker_types($lg) {
        $markers_in = db_Node::get_node($this->markers_in);
 
        if (!$markers_in) {
            Log::debug("No marker category node '".$this->markers_in."' using single marker type");
            return array(false => $this->default_marker);
        }
        
        $nodelist = NodeTree::build_flat($markers_in, array('active'));
        array_shift($nodelist); // remove category node from list
        
        $marker_types = array();
        foreach ($nodelist as $nodeinfo) {
            $id = $nodeinfo['node']->id;
            $content = $nodeinfo['node']->get_content($lg);
            if ($content && $content->active) {
                $name = $content->title();
                $selection_name = str_repeat("&nbsp;&nbsp;", count($nodeinfo['connections'])).$content->title();
                $symbol = $content->symbol();
                $icon   = $symbol['file'];
                $marker_types[] = array(
                    'id' => $id, 
                    'value' => $id, // deprecated
                    'selection_name' => $selection_name,
                    'name' => $name,
                    'icon' => $icon
                );
            }
        }

        return $marker_types;
    }
}
