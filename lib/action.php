<?php
require_once('lib/action_interfaces.php');

/** Represent actions
 * @package Aquarius
 * Actions can be passed in URIs as strings, they look like this:
 *   action[node:edit:23][]
 * which would be translated to an object instance of class 'action_node'.
 * The use of the square brackets prevents two problems:
 *   1. PHP translates dots (.) to underscores (_) for GET and POST variable names, but doesn't touch array keys. Thus we write the action string as key of an array.
 *   2. If the actions are used in 'input type="image"' buttons as 'name' parameters, browsers append .x and .y to pass the mouse coordinates over the button. With the appended '[]' these vars are put in their own array
 */

class Action implements BasicAction {
    
    /** Properties of the action, subclasses add their own props */
    var $props = array('class');
    /** Subclasses can add properties that are stored in key=value format in the url */
    var $named_props = array();
    /** After mapping all parameters to properties, the rest of parameters are put into the params array. */
    var $params = array();
    /** Sequence counter to give actions unique and increasing sequence numbers */
    static $max_sequence = 0;

    /** Using the dollar '$' char to separate action parameters because it
      * doesn't require escaping in URL and is rarely used. */
    static $param_separator = '$';
    
    /** Escape codes for the action parameter escaping */
    private static $param_repl;
    private static $param_subst;
    function init_static() {
        self::$param_repl  = array(self::$param_separator, '[', ']');
        self::$param_subst = array('%'.ord(self::$param_separator), '%'.ord('['), '%'.ord(']'));
    }
    
    
    /** Title of this action
      * Child classes may provide a title by overriding this method. The title may vary depending on the action parameters.
      */
    function get_title() {
        return null;
    }

    /** Icon for action
      * Child classes may provide an icon filename by overriding this method.
      */
    function get_icon() {
        return null;
    }

    /** Initialization method, executed after construction
      * Called on right after construction, when action parameters have been assigned, but before permissions have been checked.
      * @return true if the action is valid
      * Override this method to initialize your action and check for invalid parameters.
      */
    function init($aquarius) {
        return true;
    }

    /** Parse action parameters and build an action
      */
    static function parse_parameters($paramstr) {
        $actionparams = explode(self::$param_separator, $paramstr); // Split into parameters
        $params = array();
        $named_params = array();
        foreach($actionparams as $param) {
            $key_end = strpos($param, '=');
            if ($key_end == false) {
                $params []= rawurldecode($param);
            } else {
                $key =   rawurldecode(substr($param, 0, $key_end));
                $value = rawurldecode(substr($param, $key_end + 1));
                $named_params[$key] = $value;
            }
        }
        $action = Action::build($params, $named_params);
        if (!$action) Log::info("Could not build requested action for '".join(self::$param_separator, $actionparams)."', permission denied? ");
        return $action;
    }

    /** Parse an action string.
      * The reverse to the actionstr() method: Action::parse($action->actionstr()) ~= $action
      */
    static function parse($actionstr) {
        // Check the trimmings
        $start = substr($actionstr, 0, 7);
        $params = substr($actionstr, 7, -3);
        $end = substr($actionstr, -3, 3);
        if ($start != 'action[' || $end != '][]') throw new Exception("Invalid action string: '".$actionstr."'");

        // Build the action
        return Action::parse_parameters($params);
    }
    
    /** Builds the list of actions contained in the $request. 
      * $request might look like this:
            http://admin.aqualan/admin.php?lg=de&action[0:nodetree:show][]&action[58:contentedit:edit:17:de][]
        when parsed by PHP:
            $request = array('action'=>array('0:nodetree:show'=>array()), '58:contentedit:edit:17:de'=>array()))
      */ 
    static function request_actions($request) {
        $actions = array();
        $actionstrs = get($request, 'action', array());
        foreach ($actionstrs as $keystr => $str) {
            // The action string may either be passed as key or as value. Check whether $keystr is numeric we then assume it's the value
            if (is_numeric($keystr)) {
                $actionstr = $str;
            } else {
                $actionstr = $keystr;
            }
            $action = Action::parse_parameters($actionstr);
            if ($action)
                $actions[] = $action;
            else
                Log::info("Could not build requested action for '".$actionstr."' (permission denied?) ");
        }
        return $actions;
    }
    
    /** Checks that this action may be used.
      * This includes checking the user's authorization but also whether the action is plausible.
      * Subclasses must override the permit() method because this one always returns false.
      */
    function permit() {
      return false;
    }
    
    /** Perform the action, child classes override this method to implement their stuff.
      * @return array 
      */
    function execute() {
        throw new Exception("Action not implemented.");
    }

    /** String representation of the action, uses $this->actionstr() */
    public function __toString() {
        return $this->actionstr();
    }

    /** Turn action parameters into urlencoded and separated string */
    function simple_actionstr() {
        $vals = array();
        if (isset($this->sequence)) $vals[] = $this->sequence; // Prepend the sequence number if there is one
        foreach ($this->props as $key)         $vals[] = $this->encode_param($this->$key);
        foreach ($this->params as $param)      $vals[] = $this->encode_param($param);
        foreach ($this->named_props as $key) {
            if (strlen($this->$key) > 0) {
                $vals[] = $this->encode_param($key).'='.$this->encode_param($this->$key);
            }
        }
        return join(self::$param_separator, $vals);
    }
    
    function encode_param($param) {
        return str_replace(self::$param_repl, self::$param_subst, rawurlencode($param));
    }
    
    /** Serialize this action into an url name of the form 'action[param0:param1:...][]' */
    function actionstr() {
        return "action[".$this->simple_actionstr().'][]';
    }

    /** Load action class
      * Ensure the class for the given action name is loaded and return the full class name of the action
      * @param $name Name of the action
      * @return full name of the action class
      * This method throws an exception if the action class could not be loaded
      *
      * Example of how to call a static method of an action:
      * $action_class = Action::use('someaction');
      * $action_class::somemethod();
      */
    static function use_class($name) {
        $classname = "action_".$name;

        // Load the class if neccessary
        if (!class_exists($classname, false)) {
            // Include the code for our action class.
            $includename = "lib/action/".basename(strtolower($name)).".php";
            if (!include $includename) throw new Exception("Failed loading action class '$classname' in '$includename'");
            if (!class_exists($classname, false)) throw new Exception("Loaded '$includename' but class '$classname' still missing");
        }
        return $classname;
    }

    /** Convenience wrapper around Action::build().
     * Allows to create an action like this:
     * Action::make('somecommand', 'someoption', '42', 'yeah');
     */
    static function make() {
        $params = func_get_args();
        return Action::build($params);
    }

    /** Build an action for given parameters.
     * This loads the action's class dynamically. After construction, the action is checked for permission. False is returned if there's no permission for this action. 
     */
    static function build($params, $named_params = array()) {
        // If the first param is a number we assume that it's a sequence number for the action
        $sequence = false;
        if (is_numeric($params[0])) {
            $sequence = array_shift($params);
        }
        
        // Class name of the action is the first parameter
        $class = $params[0];
        $classname = self::use_class($class);

        if (isset($params[1])) {
            $sub_action_class = $classname.'_'.$params[1];
            if (class_exists($sub_action_class, false)) {
                $classname = $sub_action_class;
            }
        }

        // Now create the thing
        $thing = new $classname($params, $named_params, $sequence);

        // Initialize the action
        global $aquarius;
        $valid = $thing->init($aquarius);
        if (!$valid) return false;

        // Check that the user has permission to use the action
        if ($thing->permit())
            return $thing;
        else
            return false; // We do NOT throw an exception here, it is explicitly permitted to try building actions
    }

    /** Constructor that writes the params to properties of the action.
      * Say you have $params=["node","delete","10"] and $this->props=["class","command","id"] you'd get an object where $this->class = "node", $this->command = "delete" and $this->id = 10
      * Additional params not claimed by a prop will be put into $this->params.
      *
      * @param $params list of action parameters, must be as long or longer than $this->props
      * @param $sequence sequence number override. A new sequence number is generated on construction, if this is not given. Note that using this parameter sets the sequence counter so that all actions constructed after this will have a higher sequence number.
      * Use Action::make() or Action::build() to create an action.
      */
    protected function __construct($params, $named_params = array(), $sequence = false) {
        // Map the parameters to object properties
        foreach($this->props as $index => $prop) {
            $param = array_shift($params);
            if ($param !== NULL) { // Parameters can have a value of false, mind you
                $this->$prop = str($param);
            } else {
                throw new Exception("Expected parameter '$prop' at index '$index' for ".get_class($this));
            }
        }

        // Map named params
        foreach($this->named_props as $key) {
            $this->$key = get($named_params, $key, null);
        }

        // Store the rest of the parameters
        $this->params = $params;

        // Initialize sequence
        $this->sequence($sequence);
    }
    
    /** Generate a sequence number for this action or register a given sequence number.
      * The idea is that newly created actions have a higher sequence number than the ones received from the request
      */
    function sequence($sequence = false) {
        if ($sequence !== false) {
            Action::$max_sequence = max($sequence+1, Action::$max_sequence);
            $this->sequence = $sequence;
        } else {
            $this->sequence = Action::$max_sequence++;
        }
    }
    
    /** Generates new sequence number for the clone */
    function __clone() {
        $this->sequence();
    }

    /** Whether two actions are equal.
      * Compares the actions' class and properties for equality (non-strict).
      * The sequence value is ignored. */
    function equals($thing) {
        if ($thing instanceof Action) {
            if (get_class($this) == get_class($thing)) {
                foreach($this->props as $prop) {
                    if ($this->$prop != $thing->$prop) return false;
                }
                foreach($this->named_props as $prop) {
                    if ($this->$prop != $thing->$prop) return false;
                }
                if ($this->params != $thing->params) return false;
                return true;
            }
        }
        return false;
    }

    /** Whether this action changes things */
    function is_change() {
        return $this instanceOf ChangeAction;
    }
    
}

// PHP doesn't allow function calls in static var initializers. Workaround:
Action::init_static();
