<?
/** @package Aquarius */

/** Filters accept or reject things */
interface Filter {
    /** See whether a given thing passes the filter
      * @param $thing to be applied to the filter
      * @return boolean value indicating whether $thing passed the filter */
    function pass($thing);
}

class Filter_Logic_True implements Filter {
    function pass($thing) {
        return true;
    }
}

class Filter_Logic_Not implements Filter {
    function __construct(Filter $clause) {
        $this->clause = $clause;
    }

    function pass($thing) {
        return !$this->clause->pass($thing);
    }
}

class Filter_Logic_And implements Filter {
    function __construct(array $clauses) {
        $this->clauses = $clauses;
    }

    function pass($thing) {
        foreach($this->clauses as $clause) {
            if (!$clause->pass($thing)) return false;
        }
        return true;
    }
    
    function sql_predicates($query) {
        $predicates = array('1=1');
        foreach($this->clauses as $filter) {
            $predicates []= $filter->sql_predicates($query);
        }
        return '('.join(' AND ', $predicates).')';
    }
}

class Filter_Logic_Or implements Filter {
    function __construct(array $clauses) {
        $this->clauses = $clauses;
    }

    function pass($thing) {
        foreach($this->clauses as $clause) {
            if ($clause->pass($thing)) return true;
        }
        return false;
    }
    
    function sql_predicates($query) {
        $predicates = array('0=0');
        foreach($this->clauses as $filter) {
            $predicates []= $filter->sql_predicates($query);
        }
        return '('.join(' OR ', $predicates).')';
    }
}

class Filter_Custom implements Filter {
    function __construct($callback, $argument = null) {
        $this->callback = $callback;
        $this->argument = $argument;
    }

    function pass($thing) {
        return call_user_func_array($this->filter_function, array($thing, $this->argument));
    }
}

/** Filter based on content field */
class NodeFilter_Field implements Filter {
    function __construct($field, $value) {
        $this->field = $field;
        $this->value = $value;
    }

    function pass($thing) {
        return $thing->{'get_'.$this->field}() == $this->value;
    }
    
    function sql_predicates($query) {
        $field_id = $query->new_id();
        $cf_field = 'cf_'.$field_id;
        $query->add_join("JOIN content_field $cf_field ON content.id = $cf_field.content_id");
        $cfv_field = 'cfv_'.$field_id;
        $query->add_join("JOIN content_field_value $cfv_field ON ($cf_field.id = $cfv_field.content_field_id AND $cf_field.name = '$this->field')");
        return "$cfv_field.value = '".mysql_real_escape_string($this->value)."'";
    }
}

/** Filter that passes only active nodes */
class NodeFilter_Active implements Filter {
    function pass($thing) {
        return $thing->active == true;
    }
    
    function sql_predicates($query) {
        return "(node.active = 1 AND content.active = 1)";
    }
}

/** Filter nodes a given user does not have access to */
class NodeFilter_Login_Required implements Filter {
    /** @param $user check access permissions for this user. Preset: no user
      * @param $pass_root filter always passes restriction node itself. Preset: false. */
    function __construct($user = false, $pass_root = false) {
        $this->user = $user;
        $this->pass_root = $pass_root;
    }

    function pass($node) {
        $restriction_node = $node->access_restricted_node();
        if ($restriction_node) {
            if ($this->pass_root && $restriction_node->id == $node->id) return true;
            if ($this->user) {
                return $this->user->hasAccessTo($restriction_node->id);
            }
            return false;
        }
        return true;
    }
}

/** Node Filter
  * Filters have two parts:
  *     0: a function taking a node and an argument as parameters and returns a boolean and thus signals whether the given node passed the filter
  *     1: the argument to the filter function
  * Example, a filter that accepts only nodes with the title "Main", "Example Title" or "something":
  *     $mytitlefilter = new NodeFilter(
  *         create_function('$node, $names', 'return in_array($node->title, $names)'),
  *         array('Main', 'Example Title', 'something')
  *     ));
  * Instead of using an anonymous function you can define it first:
  *     function title_filter($node, $names) { return in_array($node->title, $names)'); }
  *     $mytitlefilter = new NodeFilter(
  *         "title_filter",
  *         array('Main', 'Example Title', 'or something')
  *     ));
  * You can then ask the filter whether a given node passes it:
  *     $passed = $mytitlefilter->pass($node);
  *
  * This class has predefined methods to create node filters. These can be constructed with the create() class method.
  * Example: NodeFilter::create('active') // To filter out inactive nodes
  * Example 2: NodeFilter::create('not', NodeFilter::create('form', 34)) // To filter out nodes with form id 34
  *
  * In the following list of filter methods, if the $arg argument isn't described, it means that boolean true can be passed to  negate the filter. So self::filter('all') would reject all nodes, whereas self::filter('all', true) would pass all nodes.
  * Logical Filters:
  *   all  filter all (kind of a no-op)
  *   not  Inverses the meaning of the filter given as argument
  *   and  All filters passed in array given as argument must pass
  *   or   One of the filters in array given as argument must pass
  *
  * Node filters:
  *   id                   Node's ID matches the ID given as arg
  *   ids                  Node's ID is one of the ID given as array in $arg
  *   name                 The name of the node must match the argument
  *   names                The name of the node must match one of the strings passed as array-argument
  *   nodes                The node is in the list of nodes passed as argument.
  *   active               Node and its parents must be active
  *   active_self          Node must be active (ignores parents active status)
  *   form                 Node's form equals the form id given in $arg
  *   access_restricted    Node has the 'access_restricted' flag set
  *   newer                Node was created within the last $arg seconds
  *   newerchange          Node was modified within the last $arg seconds
  *   has_content          pass if the node has content for the $arg language (the content must be active)
  *   user_edit_permission pass if the user given as argument has edit permission on the node
  *   show_in_menu         nodes form has show in menu flag set
  *   depth                node must be at one of the depths given in array argument
  *   require_fe_login     the current fe user may not view
  *   parent_fallthrough   the node's form has fall_through = parent
  *
  * Node structure filters:
  *   ancestor_of          node must be in the parent list of one of the nodes passed as argument
  *   descendant_of        node must be a descendant of one of the nodes passed as argument
  *   boxed                node must be boxed
  *   box                  node must be a box
  *   categorychilds       filter nodes that are boxed but not content nodes (?)
  *   contentchilds        filter nodes that are content nodes (?)
  *   category             pass category nodes
  *   content              filter content nodes (?)
  *   depth                node must be at one of the depths given in array argument
  *   max_depth            node's depth must be at most $arg
  *   has_children         node must have children, active or inactive ones
  */
class NodeFilter {
    var $filter_function;
    var $argument;

    /** Create a filter instance using the predefined filter methods of the class.
      * @param $filter_name Name of the filter method to use (without the 'filter_' prefix)
      * @param $argument The argument to the filter */
    static function create($filter_name, $argument = false) {
        $method_name = "filter_".$filter_name;
        $filter_func = array('NodeFilter', $method_name);

        if (is_callable($filter_func)) {
            return new NodeFilter($filter_func, $argument);
        } else {
            throw new Exception("No filter method '$method_name' defined in NodeFilter");
        }
    }

    /** Create a filter instance with a custom filter function.
      * @param $filter_function A callback type to a function that takes two parameters ($node and $arg) and returns a boolean (pass)
      * @param $argument The argument to the filter */
    function __construct($filter_function, $argument) {
        $this->filter_function = $filter_function;
        $this->argument = $argument;
    }

    /** Whether the given node passes this filter */
    function pass($node) {
        return call_user_func_array($this->filter_function, array($node, $this->argument));
    }

    // Logical filters
    static function filter_all($node, $arg) { return $arg; }
    static function filter_not($node, $arg) { return !$arg->pass($node); }
    static function filter_and($node, $arg) { foreach($arg as $filter) if (!$filter->pass($node)) return false; return true; }
    static function filter_or ($node, $arg) { foreach($arg as $filter) if ($filter->pass($node)) return true; return false; }

    // Node specific filters
    static function filter_id($node, $arg) { return $node->id == $arg; }
    static function filter_ids($node, $arg) { return in_array($node->id, $arg); }
    static function filter_name($node, $arg) { return $node->name == $arg; }
    static function filter_names($node, $arg) { return in_array($node->name, $arg); }
    static function filter_nodes($node, $arg) { foreach($arg as $n) if ($n->id == $node->id) return true; return false; }
    static function filter_boxed($node, $arg) { return $arg == $node->is_boxed(); }
    static function filter_box($node, $arg) { return $arg == $node->is_box(); }
    static function filter_categorychilds($node, $arg) { return $arg == ($node->box_depth() > 1); }
    static function filter_contentchilds($node, $arg) { return $arg == ($node->box_depth() == 1); }
    static function filter_category($node, $arg) { return $arg == $this->is_category(); }
    static function filter_content($node, $arg) { return $arg == $node->is_content(); }
    static function filter_active($node, $arg) { return (bool)$arg == (bool)$node->active(); }
    static function filter_active_self($node, $arg) { return (bool)$arg == (bool)$node->active(true); }
    static function filter_form($node, $arg) { $form = $node->get_form(); return $form && $form->id == $arg; }
    static function filter_access_restricted($node, $arg) { return (bool)$node->access_restricted == $arg; }
    static function filter_newer($node, $arg) { return $node->created > time() - $timespan; }
    static function filter_newerchange($node, $arg) { return  $node->last_change > time() - $timespan; }
    static function filter_has_content($node, $arg) { return (bool)$node->get_content($arg,true); }
    static function filter_user_edit_permission($node, $arg) { return $arg->may_edit($node); }
    static function filter_show_in_menu($node, $arg) { $form = $node->get_form(); return $arg == ($form && $form->show_in_menu); }
    static function filter_depth($node, $arg) {return !in_array($node->depth(), $arg);} 
    static function filter_max_depth($node, $arg) {return $node->depth() <= $arg;}
    static function filter_has_children($node, $arg) {return $arg == ($node->cache_left_index != $node->cache_right_index);}
    static function filter_parent_fallthrough($node, $arg) { return $arg == ($node->get_form()->fall_through == 'parent'); }


    static function filter_ancestor_of($node, $nodes) {
        foreach($nodes as $child_node) if ($child_node->descendant_of($node)) return true;
        return false;
    }
    
    static function filter_descendant_of($node, $nodes) {
        foreach($nodes as $child_node) if ($node->descendant_of($child_node)) return true;
        return false;
    }
}

class SQL_NodeFilter {
    var $last_id = 0;
    
    var $joins = array();
    var $wheres = array();

    function new_id() {
        return $this->last_id++;
    }

    function add_join($join) {
        $this->joins []= $join;
    }
    
    function add_where($expr) {
        $this->wheres []= $expr;
    }
    
    function add_filter($filter) {
        $this->wheres []= $filter->sql_predicates($this);
    }
    
    function run($DB, $lg) {
        $wheres = $this->wheres;
        $wheres []= "content.lg = '$lg'";
        $node_ids = $DB->listquery("
            SELECT node_id FROM node
            JOIN content ON node.id = content.node_id
            ".join("\n", $this->joins)."
            WHERE ".join("\n AND ", $wheres)."
        ");
        return db_Node::get_nodes($node_ids);
    }
}
?>