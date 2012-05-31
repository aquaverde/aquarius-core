<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.map.php
 * Type:     function
 * Name:     map
 * Purpose:  creates a google map  
 * -------------------------------------------------------------
 */
function smarty_function_map($params, &$smarty) {
    global $aquarius;
    $maps_modul = $aquarius->modules['maps'];

    $node = db_Node::get_node(get($params, 'node', $smarty->get_template_vars('node')));
    $lg = $smarty->get_template_vars('lg');

    init_map();

    $which_map = 'simple';
    if (isset($params['category'])) $which_map = 'category';
    if (isset($params['overcat']))  $which_map = 'overcat';

    if($which_map == 'simple') {
        get_simple_map($maps_modul->get_content_list_node($node->id, $lg), $smarty);
    }

    if($which_map == 'category') {
        $cat = $params['category'];
        if($cat == "all") {
            get_all_map($xml);
            if(isset($params['preview'])) {
                define('PREVIEW_CAT',$params['preview']);
                echo "
                    <script type=\"text/javascript\" charset=\"utf-8\">
                        hide_all();
                    </script>";
                echo "
                    <script type=\"text/javascript\" charset=\"utf-8\">
                        show_cat('".$params['preview']."');
                    </script>";
            }
            elseif(isset($params['preview_overcat'])) {
                define('PREVIEW_CAT',$params['preview_overcat']);
                echo "
                    <script type=\"text/javascript\" charset=\"utf-8\">
                        hide_all();
                    </script>";
                echo "
                    <script type=\"text/javascript\" charset=\"utf-8\">
                        show_over_cat('".$params['preview_overcat']."');
                    </script>";
            }
            else {
                define('PREVIEW_CAT',"all");
            }
        }
        else {
            get_category_map($cat,$content_list);
        }
    }

    if($which_map == 'overcat') get_overcat_map($params['overcat'],$content_list);
}

function get_simple_map($content_list,&$smarty) {
	$smarty->assign("map_content", $content_list);
	// $replacer = array(chr(10) => "<br/>", chr(13) => "", chr(39) => "\'");
	// foreach($content_list as $marker) {
	// 	if($marker['node_id'] == $smarty->get_template_vars('node')->id) {
	// 		if($marker['type'] != "poly") {
	// 			echo "
	// 				<script type=\"text/javascript\" charset=\"utf-8\">
	// 				addMarker('".$marker['lat']."','".$marker['lng']."','".strtr(htmlspecialchars($marker['title']),$replacer)."','".strtr(htmlspecialchars($marker['desc']),$replacer)."','".$marker['th_pic']."','".$marker['link']."','".$marker['link_text']."','".$marker['cat']."','".$marker['icontype']."');
	// 				</script>";
	// 		}
	// 		else {
	// 			echo "
	// 				<script type=\"text/javascript\" charset=\"utf-8\">
	// 					addPoly('".$marker['lat']."','".$marker['lng']."');
	// 				</script>";
	// 		}
	// 	}
	// }
	// echo "
	// 	<script type=\"text/javascript\" charset=\"utf-8\">
	// 		if(gmarkers.length > 0)
	// 			setBoundCenter();
	// 	</script>";	
}

function get_category_map($cat,$content_list) {
	$replacer = array(chr(10) => "<br/>", chr(13) => "", chr(39) => "\'");		
	foreach($content_list as $marker) {
		if($marker['cat'] == $cat) {
			if($marker['type'] != "poly") {
				echo "
					<script type=\"text/javascript\" charset=\"utf-8\">
					addMarker('".$marker['lat']."','".$marker['lng']."','".strtr(htmlspecialchars($marker['title']),$replacer)."','".strtr(htmlspecialchars($marker['desc']),$replacer)."','".$marker['th_pic']."','".$marker['link']."','".$marker['link_text']."','".$marker['cat']."','".$marker['icontype']."');
					</script>";
			}
			else {
				echo "
					<script type=\"text/javascript\" charset=\"utf-8\">
						addPoly('".$marker['lat']."','".$marker['lng']."');
					</script>";
			}
		}
	}
	echo "
		<script type=\"text/javascript\" charset=\"utf-8\">
			if(gmarkers.length > 0)
				setBoundCenter();
		</script>";
}

function get_overcat_map($overcat,$content_list) {
	foreach($content_list as $marker) {
		if(isset($marker['over_cat']) && $marker['over_cat'] == $overcat) {
			if($marker['type'] != "poly") {
				echo "
					<script type=\"text/javascript\" charset=\"utf-8\">
					addMarker('".$marker['lat']."','".$marker['lng']."','".$marker['title']."','".$marker['desc']."','".$marker['th_pic']."','".$marker['link']."','".$marker['link_text']."','".$marker['cat']."','".$marker['icontype']."');
					</script>";
			}
			else {
				echo "
					<script type=\"text/javascript\" charset=\"utf-8\">
						addPoly('".$marker['lat']."','".$marker['lng']."');
					</script>";
			}
		}
	}
	echo "
		<script type=\"text/javascript\" charset=\"utf-8\">
			if(gmarkers.length > 0)
				setBoundCenter();
		</script>";
}

function get_all_map($xml) {
	echo "
		<script type=\"text/javascript\" charset=\"utf-8\">
			init_map('".$xml."');
		</script>";
}

function init_map() {
		global $smarty;
		echo '
			<script type="text/javascript">
				var route_berechnen = "'.translate("route_berechnen").'"
				var hierher = "'.translate("hierher").'"
				var von_hier = "'.translate("von_hier").'"
				var startadresse = "'.translate("startadresse").'"
				var los = "'.translate("los").'"
				var zu_fuss = "'.translate("zu_fuss").'"
				var auto = "'.translate("auto").'"
				var zieladresse = "'.translate("zieladresse").'"
				var online_buchen = "'.translate("online_buchen").'"
			
				var d_lat = '.MAP_DEFAULT_LAT.'
				var d_lng = '.MAP_DEFAULT_LNG.'
				var d_zoom = '.MAP_DEFAULT_ZOOM.'
				
				var d_color = "'.POLY_COLOR.'"
				var d_width = '.POLY_WIDTH.'
				var d_trans = '.POLY_TRANS.'
				
				var d_icon = "'.MAP_DEFAULT_ICONTYPE.'"
				
				var map_key = "'.MAP_KEY.'"
				var map_xml_file = "'.PROJECT_URL.'cache/maps_extern_'.$smarty->get_template_vars('lg').'.json";								
			</script>					
			
			<script src="http://maps.google.com/maps?file=api&amp;hl='.$smarty->get_template_vars('lg').'&amp;v=2&amp;key='.MAP_KEY.'" type="text/javascript"></script>
			<script type="text/javascript" charset="utf-8" src="/lib/maps/googlemaps.js"></script>				
		';
	}



?>