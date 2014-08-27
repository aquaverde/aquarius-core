<?php
/** @package Aquarius */

/** Nodes form the tree-structure of the website.
  * Each node
  *   - represents a (language independent) page on the site
  *   - has a parent node (except for the root node)
  *   - may have content in the defined languages
  *   - May reference forms to use when presenting/editing its content or that of its children
  *
  * The tree structure is usually not modified by users of the backend, this is reserved for superadmins. The only way for users to add to the tree is possible within box-nodes. Box nodes are special nodes that specify to what depth the users may create child nodes. Children of box-nodes are 'boxed' and are further distinguished into 'category' and 'content' nodes. Category nodes are nodes where the user can add children, content nodes are at the bottom of the tree where the user is not allowed to add children.
  *
  * If the node itself does not reference a form, the 'childform' of the first parent that defines it is inherited. Content nodes inherit the 'contentform' instead.
  *
  * DB Fields prefixed with 'cache_' contain inherited values and must be updated if a parent node is changed.
  * The methods that use the cached fields all have code to calculate the value if the cached value is not present, this is important for it allows the use of transitory node instances by setting just the parent_id.
  */
class db_Node extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    public $__table = 'node';                            // table name
    public $id;                              // int(10)  not_null primary_key unsigned auto_increment group_by
    public $name;                            // varchar(750)  multiple_key
    public $parent_id;                       // int(10)  not_null multiple_key unsigned group_by
    public $form_id;                         // int(10)  multiple_key unsigned group_by
    public $childform_id;                    // int(10)  multiple_key unsigned group_by
    public $contentform_id;                  // int(10)  multiple_key unsigned group_by
    public $box_depth;                       // int(10)  not_null multiple_key unsigned group_by
    public $weight;                          // int(10)  not_null multiple_key unsigned group_by
    public $access_restricted;               // tinyint(1)  not_null multiple_key group_by
    public $created;                         // timestamp(19)  not_null unsigned zerofill
    public $last_change;                     // int(10)  unsigned group_by
    public $active;                          // tinyint(1)  not_null multiple_key group_by
    public $title;                           // varchar(750)  
    public $cache_active;                    // tinyint(1)  multiple_key group_by
    public $cache_childform_id;              // int(11)  multiple_key group_by
    public $cache_contentform_id;            // int(11)  multiple_key group_by
    public $cache_depth;                     // int(11)  multiple_key group_by
    public $cache_box_depth;                 // int(11)  multiple_key group_by
    public $cache_access_restricted_node_id;    // int(11)  multiple_key group_by
    public $cache_left_index;                // int(11)  multiple_key group_by
    public $cache_right_index;               // int(11)  multiple_key group_by

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('db_Node',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

    /** Load the root node.
      * Creates a new root if it doesn't exist */
    static function get_root() {
        $rootnode = DB_DataObject::factory('node');
        $rootnode->parent_id = 0;
        $found = $rootnode->find(true);
        switch ($found) {
        case 0: // There must always be a root node, so we create one if none exists
            $rootnode->insert();
            $rootnode->find();
            Log::info("Created root node (id $rootnode->id)");
            break;
        case 1: // Good
            break;
        default:
            Log::warn("Found $found root nodes! Using the one with id $rootnode->id please delete the other.");
        }

        return $rootnode;
    }

    /** Load the node object related to a thing, which can be:
      * - Numeric node id
      * - A node object
      * - A content object
      * - The string "root"
      * - The name_id of a node
      * False is returned for everything else.
      *
      * This method exists mainly for convenience in frontend plugins where you're never sure what exactly you're getting as parameter. */
    static function get_node($thing) {
        if (is_numeric($thing)) {
            $node = new self();
            $node->id = intval($thing);
            if ($node->find(true)) return $node;
        } elseif ($thing instanceof db_Node) {
            return $thing;
        } elseif ($thing instanceof db_Content) {
            return $thing->get_node();
        } elseif ($thing == "root") {
            return self::get_root();
        } elseif (is_string($thing) && !empty($thing)) {
            $node = DB_DataObject::factory('node');
            $node->name = $thing;
            $found = $node->find();
            if ($found > 1) Log::warn("$found nodes have name '$thing'");
            if ($found > 0) {
                $node->fetch();
                return $node;
            }
        }
        return false;
    }

    /** Load a list of nodes
      * Uses the get_node method to load nodes from a comma separated list of ids or an array of nodes/content.
      * Always returns an array of nodes, sometimes empty.
      */
    static function get_nodes($things) {
        // Split strings into array
        if (is_string($things)) {
            $things = explode(',', $things);
        }
        
        // Handle invalid input 
        if (!is_array($things)) {
            return array();
        }

        // Use db_Node::get_node on each element in the array and preserve only valid entries
        $nodes = array();
        foreach($things as $thing) {
            $node = self::get_node($thing);
            if ($node) $nodes[] = $node;
        }
        return $nodes;
    }

    /** Try to inherit a value from the parent by calling $parent->$name(), returns $default if there's no parent */
    function _inheritval($name, $default = false) {
        $parent = $this->get_parent();
        return $parent ? $parent->$name() : $default;
    }
    
    /** Get the depth of the node in the tree
      * The root node is at depth 0
      */
    function depth() {
        if ($this->cache_depth !== null) {
            return $this->cache_depth;
        } else {
            return $this->_inheritval('depth', -1) + 1;
        }
    }
    
    /** Get the amount of categories below this node
      * The sense of box_depth is reverse to the depth. Thus, a box-node at depth 3 with box_depth=3 has:
      *     - category nodes at depth 4 with box_depth 2
      *     - category nodes at depth 5 with box_depth 1
      *     - content nodes at depth 6 with box_depth 0
      * The box_depths of nodes not in a box are negative.
      */
    function box_depth() {
        if ($this->cache_box_depth !== null) {
            return $this->cache_box_depth;
        } else {
            if ($this->box_depth > 0) {
                return $this->box_depth;
            } else {
                return $this->_inheritval('box_depth', 0) - 1;
            }
        }
    }
    
    /** Is this the root node? */
    function is_root() {
        return $this->parent_id == 0;
    }
    
    /** True if this node is inside a box and not itself a box */
    function is_boxed() {
        return $this->box_depth == 0 && $this->box_depth() >= 0;
    }
    
    /** Is this node a content box? */
    function is_box() {
        return $this->box_depth > 0; // Boxes have this parameter set
    }
    
    /** Is this a content node? */
    function is_content() {
        $depth = $this->box_depth();
        return $depth == 0;
    }    
    
    /** Is this a category node? */
    function is_category() {
        return $this->is_boxed() && !$this->is_content();
    }

    /** Whether this node may be shown in the frontend
      * If any parent is deactivated, this node is considered deactivated as well.
      * @param ignore_parent_status consider a node activated even if one of its parents is deactivated, default false
      */
    function active($ignore_parent_status = false) {
        if ($ignore_parent_status) return $this->active;
        if ($this->cache_active === null) return $this->active && $this->_inheritval('active', true);
        return $this->cache_active;
    }
    
    /** Returns the first node that has access_restricted set, from $this node upwards. False if this and all parents have no access restriction set. */
    function access_restricted_node() {
        if ($this->access_restricted) return $this;
        if ($this->cache_access_restricted_node_id !== null) {
            if ($this->cache_access_restricted_node_id == 0) return false;
            return self::staticGet($this->cache_access_restricted_node_id);
        } else {
            return $this->_inheritval('access_restricted_node', false);
        }
        
    }


    /** Load the form for this node */
    function get_form() {
        $form = DB_DataObject::factory('Form');
        $found = $this->form_id && $form->get($this->form_id);
        if (!$found) {
            // This is not supposed to happen...
            if ($this->is_root()) throw new Exception("No form set on root node $this");
            $parent = $this->get_parent();
            $form = array_shift($parent->available_childforms());
            if (!$form) $form = $parent->get_form();
            Log::warn("Node $this begging form $form from $parent");
        }
        return $form;
    }
    
    /** Find the available forms for children
      * @return list of avialable forms for children, empty list means no
      *         children permitted
      * 
      * If this node is a box or a boxed category, the children will inherit the
      * childform (box category) or the contentform (box content) from this node
      * or any ancestor. In these 'boxed' cases, when the form is inherited from
      * a node, only one form will be returned. This allows restricting choice
      * in certain areas, without creating excess form copies that differ in
      * childforms only.
      * 
      * For unboxed nodes, or nodes where there are no forms specified in
      * ancestor nodes, the available forms are specified in this node's form.
      */
    function available_childforms($formtype = 'form') {
        // Find out whether children are boxed
        $inherited_form = false;
        $child_box_depth = $this->box_depth() - 1;
        if ($child_box_depth > 0) {
            // Boxed category
            $inherited_form = $this->inherited_form('child');
        } elseif($child_box_depth == 0) {
            // Boxed content
            $inherited_form = $this->inherited_form('content');
        }

        if ($inherited_form) {
            $form = DB_DataObject::staticGet('db_Form', $inherited_form);
            if (!$form) {
                Log::warn("Inherited form $inherited_form in $this does not exist");
                return array();
            }
            return array($form);
        }

        $form = $this->get_form();
        if ($form) {
            return $form->child_forms();
        }
        return array();
    }

    /** Whether the current node may have children
      */
    function children_allowed() {
        // Find out whether children are boxed
        $inherited_form = false;
        $child_box_depth = $this->box_depth() - 1;
        if ($child_box_depth > 0) {
            // Boxed category
            $inherited_form = $this->inherited_form('child');
        } elseif($child_box_depth == 0) {
            // Boxed content
            $inherited_form = $this->inherited_form('content');
        }

        if ($inherited_form) {
            return (bool)$inherited_form;
        }

        return (bool)$this->get_form()->preset_child();
    }


    private function inherited_form($type) {
        $fieldname = $type.'form_id';
        if ($this->{$fieldname}) return $this->{$fieldname};
        if ($this->is_root()) return false;
        return $this->get_parent()->inherited_form($type);
    }

    /** Get array of children, content-sorted.
      * $prefilters can be used to limit the returned nodes: it's an array containig zero or more of the keywords
      *     inactive: the node and all its parents must be active
      *     inactive_self: the node must be active
      *     boxed: Exclude boxed nodes
      *     no_sort: Not a filter, tells method to skip potentially expensive sorting step
      */
    function children($prefilters = array(), $filter = false) {
        if (in_array('boxed', $prefilters) && ($this->is_box() || $this->is_boxed())) {
            return array();
        }
        $node = DB_DataObject::factory('node');
        $node->parent_id = $this->id;
        if (in_array('inactive', $prefilters)) $node->cache_active = 1;
        if (in_array('inactive_self', $prefilters)) $node->active = 1;
        $node->orderBy('weight');
        $node->find();
        $list = array();
        while($node->fetch()) {
            if (!$filter || $filter->pass($node)) {
                $list[] = clone $node;
            }
        }
        if (!in_array('no_sort', $prefilters)) self::_contentsort($list, false);
        return $list;
    }

    /** Sort (in place) the $children array based on a content field, according to form settings
      * Not only should this be in the content class, it's a bloody mess as well. Enjoy. */
    static function _contentsort(&$children) {
        if (count($children) > 1) {
             $form = $children[0]->get_form();

            if ($form && strlen($form->sort_by) > 0) {
                // we can't trust 'sort_by' to be a name
                $fieldname = preg_replace('/[^A-Za-z0-9_]/', '', $form->sort_by); 

                require_once("lib/nodesort.php");

                // Precache content and fields
                // This is not done because it's faster, but because usort() sometimes gets confused and crashes PHP when the objects in the array change bacause they're caching stuff. (This is a bug, in my opinion.)
                foreach($children as $child) {
                    $content = $child->get_content();
                    if ($content) $content->load_fields();
                }

                // Apparently usort complains when the nodes in the array get changed
                // We ignore those warnings because they're expected.
                @usort($children, array(new Nodesort($fieldname,$form), "compare"));
            }
        }
    }
    
    /** Load a child with given url title, returns false if there's no such child. */
    function get_urlchild($urltitle, $lg=false, $require_active=false) {
        if (!$lg) $lg = $GLOBALS['lg'];
        $urlchildren = $GLOBALS['aquarius']->db->listquery("
            SELECT n.id 
            FROM node n 
                JOIN content c ON n.id = c.node_id
                JOIN content_field f ON c.id = f.content_id
                JOIN content_field_value ON content_field_value.content_field_id = f.id
            WHERE n.parent_id = ? 
                AND c.lg = ?
                AND f.name = 'urltitle'
                AND content_field_value.value = ?"
            .($require_active ? "AND n.active = 1" : ""), 
            array($this->id, $lg, $urltitle)
        );
        switch(count($urlchildren)) {
            case 0:
                return false;
            case 1:
                break; // Good
            default:
                Log::warn("Found ".count($urlchildren)." childs for node $this->id that have urltitle '$urltitle'; picking one.");
        }
        return self::staticGet(array_shift($urlchildren));
    }
    
    /** Builds an identifier for this node suitable for URLs. Uses the node's urltitle if defined, else it's built from the node's id and title. */
    function get_urltitle($lg = false) {
        $content = $this->get_content($lg);
        $title = null;
        if ($content) {
            if (strlen($content->urltitle()) > 0) {
                // Use provided title without mangling
                return $content->urltitle;
            } else {
                $title = first($content->titlefields());
            }
        }
        if (empty($title)) {
            $title = $this->get_contenttitle($lg);
        }
        $title = convert_chars_url($title);
        return strtolower($title).'.'.$this->id;
    }

    /** Get parent node of this node.
      * @return the immediate parent or false */
    function get_parent() {
        if (!$this->is_root()) return db_Node::staticGet($this->parent_id);
        else return false;
    }
    
    /** Get this or the first parent node that satisfies a filter.
      * If this node satisfies the filter already, it will be returned.
      * @param $filter filter to select a parent
      * @return the matching ancestor or false if no parent matched. */
    function first_ancestor_matching($filter) {
        if ($filter->pass($this)) return $this;
        else {
            $parent = $this->get_parent();
            if ($parent) return $parent->first_ancestor_matching($filter);
            else return false;
        }
    }
    
    /** List all parents
      * Top down list, the root node is the first entry. */
    function get_parents($includeself = false) {
        $list = array();
        if ($includeself)
            $list[] = $this;
        $node = $this;
        while($node = $node->get_parent())
            array_unshift($list, $node);
        return $list;
    }

    // returns all content of a node (only for active languages)
    function get_all_content() {
        $active_languages = db_Languages::getLanguages();

        $active_content = array();
        foreach($active_languages as $my_language) {
            $content = $this->get_content($my_language);

            // is this content active?
            if ($content)
                $active_content[] = $content;
        }
        return $active_content;		
    }

    /** Get the associated content for a node.
      * Uses the default language if no $lg is given.
      * If $active is true, return the content only if it's active. Returns false if there's no content.
      */
    function get_content($lg=false, $active=false) {
        if (!is_numeric($this->id)) return false; // Invalid nodes do not have content :-)
        if (!$lg) $lg = $GLOBALS['lg'];

        $content = db_Content::get_cached($this->id, $lg);
        if (!$content or ($active and !$content->active()))
            return false;
        else
            return $content;
    }
    
    /** Get a title for this node, uses the content's title if there's one */
    function get_contenttitle($lg=false) {
        $content = $this->get_content($lg);
        if ($content) {
            $title = $content->get_title();
            if (strlen($title) > 0) {
                return $title;
            }
        }
        return $this->title;
    }
    
    /** Delete this node and all nodes below it (DB_DataObject::delete override)*/
    function delete() {
    
        global $aquarius;
        $aquarius->execute_hooks('node_delete', $this);
        
        // Delete all children
        $child = DB_DataObject::factory('node');
        $child->parent_id = $this->id;
        $child->find();
        while ($child->fetch()) {
            $child->delete();
        }
        // Delete attached content
        $cont = DB_DataObject::factory('content');
        $cont->node_id = $this->id;
        $cont->find();
        while ($cont->fetch()) {
            $cont->delete();
        }
        parent::delete(); // Call the DB_DataObject delete method
    }
    
    /** Insert this node (DB_DataObject::insert override) */
    function insert() {
        
        // Calculate a sensible weight if none has been specified
        if (!$this->weight) {
            $maxweight = array_shift($GLOBALS['DB']->listquery('SELECT max(weight) FROM node WHERE parent_id = '.$this->parent_id)); // May be NULL if the node table is empty or parent_id is invalid
            $this->weight = ($maxweight + 10) - ($maxweight % 10); // Round off to ten
        }

        $result = parent::insert();
        
        global $aquarius;
        $aquarius->execute_hooks('node_insert', $this);
    
        return $result;
    }
    
    function move($new_parent) {
        if ($this->id == $new_parent->id || $this->ancestor_of($new_parent)) {
            throw new Exception("Node ".$this->idstr()." cannot be parent unto itself.");
        }
        $this->parent_id = $new_parent->id;
        $this->update();
        
        global $aquarius;
        $aquarius->execute_hooks('node_move', $this);

    }
    
    /** Copy a node and all its children
      * @param title_append optional string to append to the titles of copied nodes and content. The title will not be appended to children
      * @param new_parent optional node to use as parent
      * @return the clone
      * WARNING: Node cache inconsistent after this operation, must be rebuilt. (The reason this function does not do this is that (1) it recurses (2) may be called many times in sequence. Rebuilding the cache everytime would be wasteful.)
      */
    function copy($title_append='', $new_parent=false) {
        $copy = clone $this;
        $copy->weight = null;
        $copy->title = $copy->title.$title_append;
        if ($new_parent) $copy->parent_id = $new_parent->id;
        $copy->name = ''; // Clones don't have names, duh
       
        $copy->insert();
        
        // Copy children and attach the duplicates to our copy
        $child = DB_DataObject::factory('node');
        $child->parent_id = $this->id;
        $child->find();
        while ($child->fetch()) {
            $child->copy('', $copy);
        }
        // Copy content
        $cont = DB_DataObject::factory('content');
        $cont->node_id = $this->id;
        $cont->find();
        while ($cont->fetch()) {
            $content_copy = clone $cont;
            $content_copy->load_fields();
            $content_copy->node_id = $copy->id;
            $content_copy->title = $content_copy->title().$title_append;
            $content_copy->insert();
        }

        global $aquarius;
        $aquarius->execute_hooks('node_copy', $this, $copy);

        return $copy;
    }
    
    /** Get an array of strings describing the thing here
      * The first entry in the returned array is always "node", the second may be "root", "rubric", "box", "category" or "content" and the third is either "on" or "off"
      */
    function get_prop() {
        $type = "rubric";
        if ($this->is_root()) $type = "root";
        if ($this->is_box()) $type = "box";
        if ($this->is_boxed())
            if ($this->is_content())
                $type = "content";
            else
                $type = "category";
        return array("node", $type, $this->active?"on":"off");
    }

    function icon() {
        return join("_", $this->get_prop());
    }


    /** Divert $this->get_*() calls to content->*()
      * DEPRECATED it's dangerous and confusing */
    function __call($name, $params) {
        $prefix = substr($name, 0, 4);
        $name = substr($name, 4);
        switch ($prefix) {
            case "get_":
                $content = $this->get_content();
                if ($content)
                    return call_user_func_array(array(&$content, $name), $params); // This abomination effects a call to $content->$name($param1, $param2...)
                else
                    return false; // Cannot divert to nonexisting content, can I?
            default: return parent::__call($name, $params); // super();
        }
    }

    /** Update cached fields and those of all children
        The cache update is done in an SQL session (not that this helps in the usual MySQL setup)
    */
    function update_cache($recurse = true) {
        global $DB;

        $DB->query("START TRANSACTION");
        
        $this->_update_cache($recurse);
            
        // Preemptively cancel the DataObject cache (I'm sorry to mess with their internals, there is nothing in the API to do this)
        $GLOBALS['_DB_DATAOBJECT']['CACHE'] = array();

        $DB->query("COMMIT");
        
        Log::debug("Cached fields rebuilt for node $this->id and below.");
    }

    /** Update cached fields recursively */
    function _update_cache($recurse) {
        // Updating the cached values works by clearing the cache and then just using the standard method to recalculate the value

        $this->cache_depth = null;
        $this->cache_depth = $this->depth();
        
        $this->cache_active = null;
        $this->cache_active = $this->active();

        $this->cache_box_depth = null;
        $this->cache_box_depth = $this->box_depth();

        $this->cache_access_restricted_node_id = null;
        $restriction_node = $this->access_restricted_node();
        $this->cache_access_restricted_node_id = $restriction_node ? $restriction_node->id : 0;

        $this->update();

        if ($recurse) {
            $child = DB_DataObject::factory('node');
            $child->parent_id = $this->id;
            $found = $child->find();
            while ($child->fetch()) {
                $child->_update_cache(true);
            }
        }
    }


    /** Update the tree index values */
    static function update_tree_index() {
        self::get_root()->_update_tree_index(0);
    }

    function _update_tree_index($left_index) {
        $this->cache_left_index = $left_index;
        $right_index = $left_index;
        foreach ($this->children(array('no_sort')) as $child) {
            $right_index = $child->_update_tree_index($right_index + 1);
        }
        $this->cache_right_index = $right_index;
        $this->update();
        return $right_index + 1;
    }

    function idstr() {
        return "'".$this->title."' (".$this->id.")";
    }

    /** Whether the given node is in the parent list */
    function descendant_of($node) {
        $node = self::get_node($node);
        return $node
            && $this->cache_left_index  > $node->cache_left_index
            && $this->cache_right_index < $node->cache_right_index;
    }

    /** Whether the given node is a descendant of this node */
    function ancestor_of($node) {
        $node = self::get_node($node);
        return $node
            && $this->cache_left_index  < $node->cache_left_index
            && $this->cache_right_index > $node->cache_right_index;
    }

    function __toString() {
        return $this->idstr();
    }
}
