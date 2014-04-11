<?php

/* Provide a google maps API key
 * The API key may also be specified on a per-domain basis in the domain-config
 */
$config['maps']['api_key'] = false;

/* Color and width of polylines when editing in the backend */
$config['maps']['polyline'] = array(
    'color' => '#2c2d2e',
    'width' => 6
);

/* Preset position */
$config['maps']['position'] = array(
    'lat' => 47.14130,
    'lon' => 7.25344,
    'zoom' => 19
);

/* Configure marker classes */
$config['maps']['marker_classes'] = array(
    'default' => array(
        'size' => array(44,45),
        'anchor' => array(22,46) // Bottom center
    )
);

