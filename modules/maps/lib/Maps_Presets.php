<?php

class Maps_Presets {
    var $api_key;
    var $markers_in = 'gmapc';
    var $default_marker = array(
            'value' => false,
            'id' => false,
            'name' => 'default',
            'selection_name' => 'default',
            'icon' => '/interface/marker.png',
            'size' => array(45,45)
    );
    
    function __construct($api_key, $position, $polyline, $markers_in, $marker_classes) {
        $this->api_key = $api_key;
        $this->position = $position;
        $this->polyline = $polyline;
        $this->markers_in = $markers_in;
        $this->marker_classes = $marker_classes;
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
            
            $class = 'default';
            if ($content) {
                $content->load_fields();
                $class = isset($content->class) ? $content->class : 'default';
                if (!isset($this->marker_classes[$class])) {
                    $class = 'default';
                }
            }
            
            $marker_class = $this->marker_classes[$class];

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
                    'icon' => $icon ? $icon : $this->default_marker['icon'],
                    'size' => $marker_class['size'],
                    'anchor' =>  $marker_class['anchor']
                );
            }
        }

        return $marker_types;
    }
}
