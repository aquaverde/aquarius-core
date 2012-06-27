<?
/** Frontend URL generator
  * Generates links valid frontend pages.
  *
  *
  * Never temporarly overwrite options like in this
  * particularly degenerate example:
  *
  *   $oldlg = $urlconstruction->lg;
  *   $urlconstruction->lg = $currentlg;
  *   $my_link = $urlconstruction->to($node);
  *   $urlconstruction->lg = $oldlg;
  *
  * Your god will go on a kitten-killing spree should you
  * do the above. Instead, do this:
  *
  * $my_link = $urlconstruction->with('lg', $currentlg)->to($node);
  * */
class FrontendUrlFactory {
    /** Target language for generated URL */
    var $lg;

    /** Base URL to build relative URLs from
      * If this is set, the factory tries to produce URLs relative to this URL. */
    var $reference_url;

    /** hostname for URL */
    var $host;

    /** basic URI */
    var $template_uri;

    /** Create a factory for frontend URI
      * @param $construction_steps is an optional list of builtin construction steps to add.
      * */
    function __construct($construction_steps = array()) {
        require_once "lib/Named_List.php";
        $this->construction_steps = new Named_List;
        foreach($construction_steps as $step_name) $this->add_step($step_name);
        
        $this->template_uri = new Url();
    }

    /** Build a URL to a page identified by node
      * @param node the node the the url should link to
      * If content is given the language of the content is used.
      */
    function to($node) {
        $options = clone $this;
        if ($node instanceof db_Content) {
            $options->lg = $node->lg;
            $options->target_node = $node->get_node();
        } else {
            $options->target_node = $node;
        }
        $uri = clone $this->template_uri;
        
        foreach($this->construction_steps as $name => $construction_step) {
            call_user_func($construction_step, $options, $uri);
        }
        return $uri;
    }

    /** Add or replace a construction step
      * When no $step is given, this class' method with the same $name is used.
      */
    function add_step($name, $step = null, $location = 'after', $relative_to = null) {
        if (!$step) {
            $step = array('FrontendUrlFactory', $name);
        }
        if (!is_callable($step)) {
            throw new Exception("Step '$name' not callable");
        }
        $this->construction_steps->add($name, $step, $location, $relative_to);
    }


    /** If the target_node falls through to parent, use the parent as target_node and add an anchor to the current node.
      *
      * The urltitle of the original node relocated away from is added as an anchor. */
    static function relocate_for_parent_fallthrough($options, $uri) {
        // Check whether relocation is necessary
        $node = $options->target_node;
        $relocate_node = $node;
        while(true) {
            $form = $relocate_node->get_form();
            if ($form->fall_through == 'parent') {
                if ($relocate_node->is_root()) {
                    throw new Exception("Fall through to parent set in form of root node");
                }
                $relocate_node = $relocate_node->get_parent();
            } else {
                break;
            }
        }

        // Add anchor to the original node
        if ($node !== $relocate_node) {
            $uri->anchor = $node->get_urltitle($options->lg);
        }

        $options->target_node = $relocate_node;
    }

    /** Move to parent if content has no content */
    static function parent_fallback_when_no_content($options, $uri) {
        $node = $options->target_node;
        while(!$node->is_root()) {
            if (!$node->get_content($options->lg, $options->require_active)) {
                $node = $node->get_parent();
            } else {
                break;
            }
        }
        $options->target_node = $node;
    }

    /** Use the configured domain in URI */
    static function use_option_host($options, $uri) {
        $uri->host = $options->host;
    }

    /** Create the path_parts list from the titles of the nodes from the root to the target_node */
    static function path_parts_from_nodes($options, $uri) {
        $node = $options->target_node;
        $parts = array();
        $nodes = array_slice($node->get_parents(true), 1);
        foreach($nodes as $node) {
            $parts []= $node->get_urltitle($options->lg);
        }
        $options->path_parts = $parts;
    }

    /** Add '.html' suffix to last path_part */
    static function add_html_suffix($options) {
        $parts = $options->path_parts;
        if (!empty($parts)) {
            $last_part = array_pop($parts);
            if (!preg_match('/\\.[[:alpha:]]+$/', $last_part)) $last_part .= '.html';
            array_push($parts, $last_part);
        }
        $options->path_parts = $parts;
    }

    /** Prefix language code to path */
    static function add_lg_prefix($options) {
        array_unshift($options->path_parts, $options->lg);
    }

    /** Turn path_parts into path and add it to URI */
    static function build_path($options, $uri) {
        $uri->path = '/'.implode('/', $options->path_parts);
    }

    /** URI construction step for plain URI (not rewritten) */
    static function plain_uri($options, $uri) {
        $uri->path = '/index.php';
        $uri->add_param('lg', $options->lg);
        $uri->add_param('id', $options->target_node->id);
    }

    /** Duplicate factory with changed option
      * @param option the option to change
      * @param value  new value for the option
      * @return duplicate of this factory where given option is changed to new value */
    function with($option, $value) {
        $clone = clone $this;
        $clone->$option = $value;
        return $clone;
    }
}

?>