<?php
/** Template related functions
  * @package Aquarius
  */

require_once "lib/db/Wording.php";

/** Dummy block function used to disable caching for parts of a template. */
function smarty_block_dynamic($param, $content, $smarty) {
  return $content;
}

/** Load a content and assign its fields to the smarty container.
  * Called from smarty plugin loadcontent  */
function assign_content_fields(&$smarty, &$params) {
    $load = true;
    $reason = false;

    // Load node and content
    $node = db_Node::get_node(get($params, 'node'));
    $lg = get($params, 'lg', $smarty->get_template_vars('lg'));
    $content = false;

    if ($node) {
        // Check permissions
        $restriction_node = $node->access_restricted_node();
        if ($restriction_node) {
            $access = false;
            $user = db_Fe_users::authenticated();
            if ($user) $access = $user->hasAccessTo($restriction_node->id);

            if (!$access) {
                $load = false;
                $reason = "User does not have access to $restriction_node->id";
            }
        }

        // Load the content
        $content = $node->get_content($lg);
        if (!$content) {
            $load = false;
            $reason = "No content for node $node->id in language $lg";
        }
    } else {
        $load = false;
        $reason = "Could not load node for '$node'";
    }

    if ($load) {
        $smarty->assign($content->get_fields());
    } else {
        Log::debug("assign_content_fields unsuccessful: $reason");
        return false;
    }
}

/** get translation for a wording identifier.
  * convenience wrapper around db_Wording::getTranslation().
  * @param $key identifier to be translated
  * @param $lg optional language code
  * @return text in desired language for identifier */
function translate($key, $lg = false) {
    if (!$lg) $lg = $GLOBALS['lg'];
    return db_Wording::getTranslation($key, $lg);
}

/**
 * returns a clear link if no http:// is set
 *
 * @param string $link 
 * @return the cleaned link
 */
function clean_link($link) {
    if (0 === strpos($link, '/')) {
        $cleanedLink = $link ;
    }
    elseif (0 === strpos($link, 'http://')) {
        $cleanedLink = $link ;
    }
    elseif (0 === strpos($link, 'https://')) {
        $cleanedLink = $link ;
    }
    else {
        $cleanedLink = "http://".$link;
    }

    return $cleanedLink;
}


/** Change image link to use a resized version of the image
 * 
 * Params:
 *   image:           path to image, required
 *   w or width:      set maximum width (example: 160)
 *   h or height:     set maximum height (example: 90)
 *   q or quality:    change JPEG quality (example: 80)
 *   crop: toggle     image cropping (preset true)
 *   c or crop_ratio: set crop ratio (example: "16:9")
 *   g or gray:       enable filter to grayscale (use 1 as value)
 *   l or blur:       give radius (in pixels) to blur image (example: 5)
 *   as:              use as preset settings taken from given directory (alt settings unless th flag set to true)
 *   th:              use th settings, not alt settings
 * 
 * When none of w, h, or 'as' is set, the directory-settings for the image
 * path are used. When there are no dir-settings either, the default image sizes
 * are used (alt size).
 * 
 * When both width and height are set, the image is cropped to fit
 * unless either crop=false or crop_ratio is set.
 * 
 * Examples:
 * 
 * Max width of logo: 120px
 * {resize image=/interface/logo.png w=120}
 * 
 * Use settings of directory pictures/content
 * {resize image=/other_pictures/an_example.jpg as=pictures/content}
 * 
 */
function smarty_function_resize($params, $smarty) {
    $image      = get($params, 'image');
    $width      = get($params, 'w', get($params, 'width', false));
    $height     = get($params, 'h', get($params, 'height', false));
    $quality    = get($params, 'q', get($params, 'quality', false));
    $gray       = get($params, 'g', get($params, 'gray', false));
    $blur       = get($params, 'l', get($params, 'blur', 0));
    $crop       = get($params, 'crop', true);
    $crop_ratio = get($params, 'c', get($params, 'crop_ratio', false));
    $as         = get($params, 'as', false);
    $th         = get($params, 'th', false);
    
    $using_dir_settings = false;
    $dir_settings = DB_DataObject::factory('directory_properties');
    if ($as) {
        $using_dir_settings = $dir_settings->load($as, false);
    }
    if (!$using_dir_settings && $width === false && $height === false) {
        $dir_settings->load(dirname($image));
        $using_dir_settings = true;
    }

    $slir_options = array();
    
    if ($using_dir_settings) {
        $max_size = $th ? $dir_settings->th_size : $dir_settings->alt_size;
        switch($dir_settings->resize_type) {
            case 'm':
                $slir_options ['w']= $max_size;
                $slir_options ['h']= $max_size;
                break;
            case 'w':
                $slir_options ['w']= $max_size;
                break;
            case 'h':
                $slir_options ['h']= $max_size;
                break;
        }
    }
    
    if ($width !== false) {
        $slir_options ['w']= $width;
    }
    
    if ($height !== false) {
        $slir_options ['h']= $height;
    }
    
    if ($crop_ratio !== false) {
        $slir_options ['c']= $crop_ratio;
    } elseif ($crop && $width !== false && $height !== false) {
       $slir_options ['c']= "{$width}x{$height}"; 
    }
    
    // Use maximum compression for PNG always
    if (preg_match('%.png$%i', $image)) {
        $quality = 10;
    }
    
    if ($quality !== false) {
        $slir_options ['q']= $quality;
    }
    
    if ($gray !== false) {
        $slir_options ['g']= (bool)$gray;
    }
    
    if ($blur) {
        $slir_options ['l']= (int)$blur;
    }

    $option_strings = "";
    foreach($slir_options as $option => $value) $option_strings []= $option.$value;

    require_once "file_mgmt.lib.php";
    $path = ensure_filebasedir_path(substr($image, 1));
    if (file_exists($path)) {
        $mtime = substr(filemtime($path), -4);
        return '/aquarius/slir/'.join('-', $option_strings).dirname($image).'/'.urlencode(basename($image)).'?cdate='.$mtime;
    }
}

/** Use an alternatively-sized version of the image (depends on directory settings) */
function smarty_modifier_alt($image) {
    return smarty_function_resize(array('image' => $image, 'quality' => 95), false);
}

/** Use a thumbnail-sized version of the image  (depends on directory settings) */
function smarty_modifier_th($image) {
    return smarty_function_resize(array('image' => $image, 'th' => true, 'quality' => 95), false);
}

/** Override the smarty provided date_format modifier
  * Passed date may be a timestamp (int) or a DateTime. A date format string in strftime() format may be passed as parameter, the format is taken from
  * config value DATE_FORMAT otherwise. */
function smarty_modifier_date_format($date, $format=DATE_FORMAT) {
    if($date > 0) {
        if ($date instanceof DateTime) {
            $date->setTimezone(new DateTimeZone(date_default_timezone_get()));
            $date = $date->getTimestamp();
        }
        return strftime($format, $date);
    }
}

/**
 * Replace internal links of the form 'aquarius-node:{node_id}' as generated by
 * the RTE with an absolute URI.
 *
 * Example of an internal link: <a href="aquarius-node:342">Ejemplo</a>
 * Same tag after processing: <a href="http://www.example/es/ejemplo.342.html">Ejemplo</a>
 *
 * @param string $text Replace links in this text
 * @param $uri url-generator to use
 * @param $context node to report in warning-log
 * @return text with links replaced
 */
function replace_aqualink($text, $uri, $context) {
    $pattern = "/<[\s]*a[\s][^>]*href=[\"']aquarius-node:([0-9]+)[\"'][^>]*>/";

    return preg_replace_callback($pattern, function($match) use ($uri, $context) {
        $node_id = $match[1];
        $node = db_Node::get_node($node_id);

        if(!$node) {
            Log::warn("Can`t find node '$node_id' for replacing in 'replace_aqualink'".($context ? ", node ".$context->idstr() : ''));
            return $match[0];
        }

        $link = $uri->to($node);

        return str_replace('aquarius-node:'.$node_id, $link, $match[0]);
    }, $text);
}