<?php 

/** Determine requested node from environment (URI, configuration, HTTP cookies, &c.)
  * The process is split into multiple steps so that additional steps can be inserted by modules.
  * Each step is a callback function taking parameters and returning them with added information.
  *
  * Many builtin steps are available as methods from this class. Additional steps may be added for custom processing.
  *
  * Detection parameters to be supplied:
  *   request: request variables from get/post requests ($_REQUEST)
  *   lg: determined language of the request
  *   server: server variables ($_SERVER)
  *
  * Special parameters to be set by detectors. If any of those is set by a step,
  * detection stops and the result is returned.
  *   found:    By setting this parameter to a node a step signals that the
  *             proper node was found
  *   notfound: By setting this parameter to a reason string a step signals that
  *             no node can be found
  *   redirect: By setting this parameter to a URI a step signals that the user
  *             agent should be redirected to that URI
  *
  * The following parameters are used between the builtin steps:
  *   current_node: the node located by the steps so far
  *   path_parts:   path elements yet to be processed
  *
  * 
  * */
class Node_Detection {
    private $steps;

    /** Create node detection process
      * @param $step_names list of builtin steps to add to the process
      */
    function __construct($step_names) {
        require_once "lib/Named_List.php";
        $this->steps = new Named_List;
        foreach($step_names as $step_name) $this->add_step($step_name);
    }

    /** Register a node detection mechanism
      * @param name The name of the step to insert. When a step with the same name exists already, it will be removed.
      * @param step Callback function taking and returning a dictionary argument. When no $step is given, this class' method with the same $name is used.
      * @param location where in the processing chain to insert the step, either 'before' or 'after' which means at the beginning or at the end of the chain or before or after the step named in the 'relative_to' parameter.
      * @param relative_to insert step relative to this name
      */
    function add_step($name, $step = null, $location = 'after', $relative_to = null) {
        if (!$step) {
            $step = array('Node_Detection', $name);
        }
        if (!is_callable($step)) {
            throw new Exception("Step '$name' not callable");
        }
        $this->steps->add($name, $step, $location, $relative_to);
    }
    
    /** Return callback function of the step with given name */
    function get_step($name) {
        return $this->steps->get($name);
    }

    /** Run processing steps until either 'found', 'notfound', or 'redirect' is set in parameters */
    function process($params) {
        foreach($this->steps as $name => $step) {
            $new_params = call_user_func($step, $params);
            if ($new_params) {
                if (!empty($new_params)) $this->log_changes($name, $params, $new_params);
                array_replace_aqua($params, $new_params);
            }
            if (isset($params['found']) || isset($params['notfound']) || isset($params['redirect'])) {
                return $params;
            }
        }
    }

    // Put log message about changes into debug log
    function log_changes($step, $old, $new) {
        $changed_str ='';
        foreach($new as $key=>$value) {
            $old_val = get($old, $key, null);
            if ($old_val !== $value) {
                $changed_str .= "\n $key: ";
                if (is_array($value)) {
                    $changed_str .= json_encode($value);
                } else {
                    $changed_str .= str($value);
                }
            }
        }
        if ($changed_str) {
            $changed_str = " changed parameters: ".$changed_str;
        }
        Log::debug("Node detection step $step".$changed_str);
    }

    /** Set the current_node parameter to the root node */
    static function root_as_current_node($params) {
        return array('current_node' => db_Node::get_root());
    }

    /** Split URI path into elements */
    static function path_parts_from_uri($params) {
        return array('path_parts' => array_filter(split("/", $params['uri']->path)));
    }

    /** Use node given as parameter 'id' in request */
    static function non_rewritten_node_id($params) {
        if (first($params['path_parts']) == 'index.php') {
            $node_id = get($params['request'], 'id');
            if (strlen($node_id) > 0) {
                $node = db_Node::get_node($node_id);
                if ($node) {
                    return array('current_node' => $node, 'path_parts' => array());
                } else {
                    return array('notfound' => "Invalid ID: ".$node_id);
                }
            }
        }
    }

    /** Remove language prefix if it exists */
    static function remove_lg_prefix($params) {
        $path_parts = $params['path_parts'];
        if (strlen(first($path_parts)) == 2) {
            array_shift($path_parts); // Remove language part
            return compact('path_parts');
        }
    }

    /** Remove .html suffix */
    static function remove_suffix($params) {
        $path_parts = $params['path_parts'];
        if (count($path_parts) > 0) {
            $last_part = array_pop($path_parts);
            $last_part = preg_replace('/\.html$/', '', $last_part);
            array_push($path_parts, $last_part);
            return compact('path_parts');
        }
    }

    /** Use domain preset node if no URI path is given */
    static function use_domain_preset($params) {
        if (empty($params['path_parts'])) {
            $node_id = $params['domain_conf']->get($params['uri']->host, 'node');
            if ($node_id) {
                $current_node = db_Node::get_node($node_id);
                if (!$current_node) throw new Exception("Invalid node '$node_id' in domain base");
                return compact('current_node');
            }
        }
    }

    /** Look for node id in path parts
      * If the last characters of a part is a dot followed by digits, the digits are interpreted as node id
      * When a node id is found, only the path_parts after that are left in the path_parts and the current node is changed to the node found by this id. */
    static function node_id_in_path($params) {
        $parts = $params['path_parts'];
        $remaining_parts = array();
        $new_node = false;
        while(!empty($parts)) {
            $part = array_pop($parts);
            preg_match('/\\.([0-9]+)$/', $part, $matches);  // See if there's a node id at the end of the urltitle

            if (isset($matches[1])) {
                $node_id = $matches[1];
                $node = db_Node::get_node($node_id);
                if ($node) {
                    $new_node = $node;
                } else {
                    $params['notfound'] = "Invalid ID '$node_id' in URI part $part";
                }
                break; // Stop looking through parts after first hit
            } else {
                array_unshift($remaining_parts, $part);
            }
        }
        $params = array('path_parts' => $remaining_parts);
        if ($new_node) $params['current_node'] = $new_node;
        return $params;
    }

    /** Process path_parts as urltitles descending from current_node */
    static function urltitle($params) {
        $current_node = $params['current_node'];
        while($urltitle = array_shift($params['path_parts'])) {
            $next_node = $current_node->get_urlchild($urltitle);
            if ($next_node) {
                Log::debug("Following urltitle '$urltitle' from ".$current_node->idstr()." to ".$next_node->idstr());
                $current_node = $next_node;
            } else {
                $params['notfound'] = "Invalid urltitle '$urltitle' after node ".$current_node->idstr();
                break;
            }
        }
        $params['current_node'] = $current_node;
        return $params;
    }

    /** Process fallthrough option
      * Moves current_node to where it should be according to the form's fallthrough option */
    static function fallthrough($params) {
        $fall_node = self::fallthrough_helper($params['current_node'], $params['lg']);
        if ($fall_node) {
            $params['current_node'] = $fall_node;
        } else {
            $params['notfound'] = "Fall through got stuck starting from ".$params['current_node']->idstr();
        }
        return $params;
    }

    /** Fall through to child nodes following form directions
      * @param $node the start node
      * @return resulting node or false if the fall through could not be followed
      */
    static function fallthrough_helper($node, $lg) {
        $form = $node->get_form();

        // Normal case, no fall through
        if (empty($form->fall_through) || $form->fall_through == 'none') return $node;

        // Special case: fall through to parent
        if ($form->fall_through == 'parent') {
            return $node->get_parent();
        }

        if ($form->fall_through == 'category' && $node->box_depth() <= 1) return $node; // Fall through only when children are categories

        // The rest of the fall through options are concerned with child nodes
        $children = $node->children(array('inactive'), NodeFilter::create('has_content', $lg));
        foreach($children as $child) {

            if ($form->fall_through == 'box' && !$child->is_box()) return $node; // Fall through only when child is a box

            Log::debug("Trying fall through to ".$child->idstr());
            $fall_node = self::fallthrough_helper($child, $lg);
            if ($fall_node) {
                return $fall_node;
            } else {
                Log::debug("No fall through possible to ".$child->idstr());
            }
        }

        // No fall through nodes were found
        return false;
    }

    /** Report the current_node as found */
    static function use_current_node($params) {
        $params['found'] = $params['current_node'];
        return $params;
    }
}
