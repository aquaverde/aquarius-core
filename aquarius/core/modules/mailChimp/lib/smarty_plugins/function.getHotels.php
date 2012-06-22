<?php

function smarty_function_getHotels($params, &$smarty) 
{ 
    global $DB;
    
    if (!$params['cat']) $smarty->trigger_error("getHotels: please define a category");
    if (!$params['parent_region']) $smarty->trigger_error("getHotels: please define a parent_region");

    $query = "SELECT * FROM j3lSleep WHERE form_de='".mysql_real_escape_string($params['cat'])."'";
    if(!empty($params['parent_region']) && $params['parent_region'] != "none")
    {
    
    	$query .= " AND region_de='".mysql_real_escape_string($params['parent_region'])."'";
    
    }
    
    $hotels = $DB->queryhash($query);

    
    if(!isset($params['hotelId']))
    {

        $forms      = array(
            'de' => array()
            , 'fr' => array()
            , 'en' => array()
            , 'it' => array()
        );
        $regions      = array(
            'de' => array()
            , 'fr' => array()
            , 'en' => array()
            , 'it' => array()
        );
        //$types    = array();
        
        $stars		= array();
        
        foreach ($hotels as &$hotel) {
        	
        	//NAMES
            $a = split(",", $hotel['name_de']);
            array_pop($a);
            $hotel['name_de'] = implode(",", $a);
            
            $b = split(",", $hotel['name_fr']);
            array_pop($b);
            $hotel['name_fr'] = implode(",", $b);
            
            $c = split(",", $hotel['name_en']);
            array_pop($c);
            $hotel['name_en'] = implode(",", $c);
            
            $d = split(",", $hotel['name_it']);
            array_pop($d);
            $hotel['name_it'] = implode(",", $d);
        
        	//FORMS
            if(!in_array($hotel['form_de'], $forms['de']) && !empty($hotel['form_de'])) $forms['de'][] = $hotel['form_de'];
            if(!in_array($hotel['form_fr'], $forms['fr']) && !empty($hotel['form_fr'])) $forms['fr'][] = $hotel['form_fr'];
            if(!in_array($hotel['form_en'], $forms['en']) && !empty($hotel['form_en'])) $forms['en'][] = $hotel['form_en'];
            if(!in_array($hotel['form_it'], $forms['it']) && !empty($hotel['form_it'])) $forms['it'][] = $hotel['form_it'];

			//REGIONS
            if(!in_array($hotel['region_de'], $regions['de']) && !empty($hotel['region_de'])) $regions['de'][] = $hotel['region_de'];
            if(!in_array($hotel['region_fr'], $regions['fr']) && !empty($hotel['region_fr'])) $regions['fr'][] = $hotel['region_fr'];
            if(!in_array($hotel['region_en'], $regions['en']) && !empty($hotel['region_en'])) $regions['en'][] = $hotel['region_en'];
            if(!in_array($hotel['region_it'], $regions['it']) && !empty($hotel['region_it'])) $regions['it'][] = $hotel['region_it'];
            
            //STARS
            if(!in_array($hotel['stars'], $stars) && !empty($hotel['stars'])) $stars[] = $hotel['stars'];

        }
        
        sort($stars, SORT_NUMERIC);
        
        //SORT REGIONS
        sort($regions['de'], SORT_STRING);
        sort($regions['fr'], SORT_STRING);
        sort($regions['en'], SORT_STRING);
        sort($regions['it'], SORT_STRING);
        
        shuffle($hotels);
        
        $smarty->assign("hotelForms", $forms);
        $smarty->assign("hotelRegions", $regions);
        $smarty->assign("hotelStars", $stars);
        $smarty->assign("hotels", $hotels);
    }
    else
    {
    	foreach ($hotels as &$hotel) {
        	
        	//NAMES
            $a = split(",", $hotel['name_de']);
            if(count($a) > 1)
            {
                array_pop($a);
                $hotel['name_de'] = implode(",", $a);
            }            
            
            $b = split(",", $hotel['name_fr']);
            if(count($b) > 1)
            {
                array_pop($b);
                $hotel['name_fr'] = implode(",", $b);
            }

            $c = split(",", $hotel['name_en']);
            if(count($c) > 1)
            {
                array_pop($c);
                $hotel['name_en'] = implode(",", $c);
            }

            $d = split(",", $hotel['name_it']);
            if(count($d) > 1)
            {
                array_pop($d);
                $hotel['name_it'] = implode(",", $d);
            }
        }
        
        $hotel 	= $DB->queryhash("SELECT * FROM j3lSleep WHERE id='".mysql_real_escape_string($params['hotelId'])."'");
        $hotel 	= $hotel[0];
        
        //NAMES
            $a = split(",", $hotel['name_de']);
            if(count($a) > 1)
            {
                array_pop($a);
                $hotel['name_de'] = implode(",", $a);
            }            
            
            $b = split(",", $hotel['name_fr']);
            if(count($b) > 1)
            {
                array_pop($b);
                $hotel['name_fr'] = implode(",", $b);
            }

            $c = split(",", $hotel['name_en']);
            if(count($c) > 1)
            {
                array_pop($c);
                $hotel['name_en'] = implode(",", $c);
            }

            $d = split(",", $hotel['name_it']);
            if(count($d) > 1)
            {
                array_pop($d);
                $hotel['name_it'] = implode(",", $d);
            }
        
        shuffle($hotels);
        
        $smarty->assign("hotel", $hotel);
        $smarty->assign("hotels", $hotels);
    }
}

?>