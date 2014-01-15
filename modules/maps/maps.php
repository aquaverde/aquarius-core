<?php 

require 'lib/formtype_googlemaps.php';

class Maps extends Module {

    var $register_hooks = array('init_form', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend');
    
    var $short = "maps";
    var $name  = "Google Maps fields";
    
    var $presets;
    
    function initialize($aquarius) {
        parent::initialize($aquarius);
        
        $this->presets = new Maps_Presets(
            $this->conf('api_key'),
            $this->conf('position', array()),
            $this->conf('polyline', array()),
            'gmapc'
        );
        
        /* DEPRECATED */
        if (defined('MAP_DEFAULT_LAT'))  $this->presets->position['lat'] =  MAP_DEFAULT_LAT;
        if (defined('MAP_DEFAULT_LNG'))  $this->presets->position['lon'] =  MAP_DEFAULT_LNG;
        if (defined('MAP_DEFAULT_ZOOM')) $this->presets->position['zoom'] = intval(MAP_DEFAULT_ZOOM);

        if (strlen($this->presets->api_key) < 1) {
            Log::debug("No maps API key configured");
        }
        
        /* DEPRECATED */
        if (!defined('MAP_KEY')) define('MAP_KEY', $this->presets->api_key);
    }


    function init_form($formtypes) {
        $mapstype = new Formtype_googlemaps('gmap');
        
        // Poor man's dependency injection
        $mapstype->presets = $this->presets;
        
        $formtypes->add_formtype($mapstype);
    }


    function create_marker($point, $node_id, $cache_title, $lg) {
 
		if(empty($point['type'])) continue;
	
		$marker = array();
		$marker['node_id'] = $node_id;
		$marker['lg'] = $lg;
		$replacer = array(chr(10) => "<br/>", chr(13) => "", chr(39) => "\'", chr(96) => "\'");
	
		//TITLE
		if(isset($point['title'])) $marker['title'] = $point['title'];
		
		else 
		{
			if(isset($value->cache_title)) $marker['title'] = $cache_title;
			else $marker['title'] = "-";
		}
		strtr(htmlspecialchars($marker['title']),$replacer);
			
		//BESCHREIBUNG
		if(isset($point['beschr']))
			$marker['desc'] = $point['beschr'];
		else
			$marker['desc'] = "-";
		strtr(htmlspecialchars($marker['desc']),$replacer);
		
		//LINK
		if(isset($point['link'])) {
		    $point['link'] = clean_link($point['link']);
		    
			$marker['link'] = $point['link'];
			$marker['link_text'] = $point['link'];
		}				
		else {
			$marker['link'] = "-";
			$marker['link_text'] = "-";
		}									
		
		//LAT & LNG
		$marker['lat'] = $point['lat'];
		$marker['lng'] = $point['lng'];
	
		if(isset($point['kat'])) {														
			$kat_node = db_Node::get_node($point['kat']);
		
			if(empty($kat_node->title)) {
				$marker['cat'] = "-";
				$marker['icontype'] = "-";				
			}
			else {
				if(empty($kat_node->name)) $marker['cat'] = $kat_node->title;
				else $marker['cat'] = $kat_node->name;

				$symbol1 = $kat_node->get_symbol();
				$symbol2 = split("/",$symbol1['file']);
				$symbol3 = $symbol2[count($symbol2) - 1];

				$marker['icontype'] = $symbol3;
			}
		}
		else {
			$marker['cat'] = "-";
			$marker['icontype'] = "-";
		}

		$marker['type'] = $point['type'];
	
		if(isset($point['poly_point'])) {
			$marker['poly_point'] = true;
			$marker['cat'] = $marker['title'];
			if(isset($last_over_cat)) $marker['over_cat'] = $last_over_cat;
			else $marker['over_cat'] = "--";
		}
		else $marker['poly_point'] = false;
	
		$marker['pic'] = "-";
		$marker['th_pic'] = "-";
	
		if($point['type'] == 'poly') {
			if(isset($point['kat'])) {
				$over_kat_node = db_Node::get_node($point['kat']);
			
				if(empty($overkat_node->name)) $marker['over_cat'] = $over_kat_node->title;
				else $marker['over_cat'] = $over_kat_node->name;
			}						
			else
				$marker['over_cat'] = "--";

			$marker['cat'] = $marker['title'];
			$last_over_cat = $marker['over_cat'];
		}								 
		
		strtr(htmlspecialchars($marker['cat']),$replacer);
		if(isset($marker['over_cat'])) strtr(htmlspecialchars($marker['over_cat']),$replacer);
			
		return $marker;	
    }

    function get_content_list_node($node_id, $lg) {
		$node = db_Node::get_node($node_id);
		$content = $node->get_content();
		$content->load_field();
		
		$result_list = array();
		if(!is_array($content->gmap)) return;

		foreach ($content->gmap as $point) {
			$result_list[] = $this->create_marker($point,$node->id,$node->title,$lg);
		}

		return $result_list;		
	}

	function get_content_list($lg) {
    	global $DB;
    	$query = "SELECT 
    				content.id
    			FROM form_field
    			JOIN node ON node.cache_form_id = form_field.form_id
 				JOIN content ON content.node_id = node.id
    			WHERE form_field.type = 'gmap' 
				AND form_field.name = 'gmap'
    			AND content.lg = '$lg' 
    			";
    	$content_id_list = $DB->listquery($query);

		$content_list = array();
		$result_list = array();
		
    	foreach($content_id_list as $index => $content_id) {  	
    		$content = db_dataObject::staticGet("db_content",$content_id);    	   		
			$content->load_fields();
    		$content_list[] = $content;
    	}

		foreach($content_list as $value) {
			if(!is_array($value->gmap)) continue;
			
			foreach ($value->gmap as $point) {
				$result_list[] = $this->create_marker($point,$value->node_id,$value->cache_title,$lg);
			}											
		}
    	return	$result_list; 	
    }
	
	function get_points($content_list) {
		$result_list = array();
		foreach($content_list as $value) {
			if(!empty($value['type']) && !$value['poly_point'] && $value['type'] != 'poly') $result_list[$value['cat']][] = $value; 
		}
		return	$result_list;
	}
	
	function get_lines($content_list) {
		$result_list = array();
		foreach($content_list as $value) {
			if(!empty($value['type']) && $value['type'] == 'poly') {
				if(!isset($result_list[$value['over_cat']][$value['cat']]))
					$result_list[$value['over_cat']][$value['cat']] = array();
			}
			if(!empty($value['poly_point']) && $value['poly_point']) {
				//$parent = db_dataObject::staticGet("db_node",db_dataObject::staticGet("db_node",$value['node_id'])->parent_id);
				//$parent_content = db_dataObject::staticGet("db_content",$parent->get_content()->id);

				$result_list[$value['over_cat']][$value['cat']][] = $value;
			}
		}
		return	$result_list;
	}
	
	function create_xml($content_list) {
		$replacer = array(chr(10) => "", chr(13) => "", chr(39) => "\'");
		$myXml = '<?xml version="1.0" encoding="UTF-8"?><markers>';
		foreach($content_list as $marker) {
			if(!empty($marker['type']) && $marker['type'] == "point") {
				$myXml .= '<marker title="'.strtr(htmlspecialchars($marker['title']),$replacer).'" desc="'.strtr(htmlspecialchars($marker['desc']),$replacer).'" pic="'.$marker['th_pic'].'" link="'.$marker['link'].'" linktext="'.$marker['link_text'].'" lat="'.$marker['lat'].'" lng="'.$marker['lng'].'" cat="'.strtr(htmlspecialchars($marker['cat']),$replacer).'" icontype="'.$marker['icontype'].'"';
					if(isset($marker['over_cat'])) $myXml .= ' over_cat="'.strtr(htmlspecialchars($marker['over_cat']),$replacer).'" '; 
				$myXml .= '/>';
			} elseif(!empty($marker['type']) && $marker['type'] == "poly") {
				$myXml .= $this->create_line($marker);
			}
		}
		$myXml .= '</markers>';
		return $myXml;
	}
	
	function create_line($marker) {
		$replacer = array(chr(10) => "", chr(13) => "", chr(39) => "\'");
		$stringer = '<line colour="'.POLY_COLOR.'" width="'.POLY_WIDTH.'" cat="'.strtr(htmlspecialchars($marker['cat']),$replacer).'"';
			if(isset($marker['over_cat'])) $stringer .= ' over_cat="'.strtr(htmlspecialchars($marker['over_cat']),$replacer).'" ';
		$stringer .= '>';
		$lats = split(",",$marker['lat']);
		$lngs = split(",",$marker['lng']);
		for($i = 0; $i < count($lats); $i++) {
			$stringer .= '<point lat="'.$lats[$i].'" lng="'.$lngs[$i].'" />';
		}
		$stringer .= '</line>';
		return $stringer;
	}
	
	function get_categories($content_list) {
		$categories = array();
		$do_add = true;
		foreach ($content_list as $marker) {
			if($marker['over_cat']) $do_add = false;
			 
			foreach($categories as $cat) {
				if($marker['cat'] == $cat) $do_add = false;
			}
			if($do_add) $categories[] = $marker['cat'];
			$do_add = true;
		}
		return $categories;
	}
	
	function get_overcats($content_list) {
		$categories = array();
		$do_add = true;
		foreach ($content_list as $marker) {
			if($marker['over_cat']) {
				foreach($categories as $cat) {
					if($marker['over_cat'] == $cat) $do_add = false;
				}
				if($do_add) $categories[] = $marker['over_cat'];
				$do_add = true;
			}
		}
		return $categories;		
	}
	
	function get_cats_from_overcats($content_list,$overcat) {
		$categories = array();
		$do_add = true;
		foreach ($content_list as $marker) {
			if($marker['over_cat']) {
				foreach($categories as $cat) {
					if($marker['cat'] == $cat) $do_add = false;
				}
				if($do_add) $categories[] = $marker['cat'];
				$do_add = true;
			}
		}
		return $categories;
	}
}
