<?php
/** Loop over content pointing to a node
    If there's no content pointing to a node, pointing to its parent are looked up
Params:
    to: The node which we are searching pointings for
    lg: 
    children_of: To limit the search to children of a node
    limit: Optional limit of iterations (default: unlimited)
    shuffle: randomize list before display
    search_parents: look up pointings in parents (default: true)
    filter: filter sentence selecting nodes that will end up in the list
    sort: set to true to sort by title, or set a field name
*/

function smarty_block_pointings($params, $content, &$smarty, &$repeat) {
    static $pointing_nodes;
    global $DB;

    // On first invocation of the block, we build the list of nodes
    if ( $repeat ) {
        $nodeparam = get($params, 'to');
        $node = db_Node::get_node($nodeparam);
        if (!$node) Log::debug("Could not load pointing node from 'to' param ".$nodeparam);
       
        $inherit = (bool)get($params, 'inherit');
        $search_parents = (bool)get($params, 'search_parents', true);
        $children_of = db_Node::get_node(get($params, 'children_of'));
        $depth = get($params, 'depth', false);
        $lg = db_Languages::ensure_code(get($params, 'lg', $smarty->get_template_vars('lg')));
        $custom_filter_sentence = get($params, 'filter');
        
        $sort = get($params, 'sort');
        if ($sort === true) {
            $sort = 'title';
        }
        
        $andchilds = get($params, 'andchilds');
        
        // Look for pointings starting from this node
        $found_nodes = array();
        $searching_node = $node;
    
        if($andchilds) $searching_node_childs = $searching_node->children();
        
        if($depth) {
            $children = $children_of->children();

            $query = "
                SELECT DISTINCT node.id
                FROM form_field
                JOIN node ON form_field.form_id = node.cache_form_id
                JOIN content ON node.id = content.node_id
                JOIN content_field ON content.id = content_field.content_id
                JOIN content_field_value ON content_field.id = content_field_value.content_field_id
                WHERE (form_field.type = 'pointing' OR form_field.type = 'pointing_legend')
                AND node.active = 1 
                AND content.active = 1 
                AND content.lg = '$lg'
                AND form_field.name = content_field.name";
            if($andchilds)
            {
                $query .= "
                AND (content_field_value.value = $searching_node->id";
                for($i = 0; $i < count($searching_node_childs); $i++)
                {
                    $child = $searching_node_childs[$i];
                    $query .= " OR content_field_value.value = $child->id";
                }
                $query .= ")";
            }
            else
            {
                $query .= "
                AND content_field_value.value = $searching_node->id";
            }

            if ($children_of) {
                $query .= "
                AND (";
                for($i = 0; $i < count($children); $i++)
                {
                    $child = $children[$i];
                    if($i != 0)
                    {
                        $query .= " OR node.parent_id = $child->id";
                    }
                    else
                    {
                        $query .= " node.parent_id = $child->id";
                    }
                }
                $query .= ")";
            }
            
            $found_nodes = $DB->listquery($query);
        } else {
            while($searching_node) {
                $query = "
                    SELECT DISTINCT node.id
                    FROM form_field
                    JOIN node ON form_field.form_id = node.cache_form_id
                    JOIN content ON node.id = content.node_id
                    JOIN content_field ON content.id = content_field.content_id
                    JOIN content_field_value ON content_field.id = content_field_value.content_field_id
                    WHERE (form_field.type = 'pointing' OR form_field.type = 'pointing_legend')
                    AND node.active = 1 
                    AND content.active = 1 
                    AND content.lg = '$lg'
                    AND form_field.name = content_field.name
                    AND content_field_value.value = $searching_node->id";

                if ($children_of) {

                        $query .= "
                        AND node.parent_id = $children_of->id";

                }
                $found_nodes = $DB->listquery($query);

                if (count($found_nodes) < 1 && $search_parents) {
                    $searching_node = $searching_node->get_parent();
                } else {
                    $searching_node = false;
                }
            }
        }
        
        foreach ($found_nodes as $fnode) {
            $node = db_Node::get_node($fnode);
            if ($node) $pointing_nodes []= $node;
        }
        
        $custom_filter = false;
        if ($custom_filter_sentence) {
            try {
                global $aquarius;
                $custom_filter = $aquarius->filterparser->interpret($custom_filter_sentence, $smarty->get_template_vars());
            } catch (FilterParsingException $filter_error) {
                $smarty->trigger_error("Failed parsing filter '$custom_filter_sentence': ".$filter_error->getMessage());
            }
        }
        
        if ($custom_filter) {
            $pointing_nodes = array_filter($pointing_nodes, array($custom_filter, 'pass'));
        }
        
        if($sort) {
            usort($pointing_nodes, Compare_Pointings::by_field($sort));
        }
        
        $limit = intval(get($params, 'limit', 0));
        if ($limit > 0) $pointing_nodes = array_slice($pointing_nodes, 0, $limit);
        
        if (get($params, 'shuffle')) shuffle($pointing_nodes);
        
    }

    // In each iteration, we check whether there's a pointing node left to display, and load its content
    $pointing_node = array_shift($pointing_nodes);
    $repeat = (bool)$pointing_node;
    if ($repeat) {
        $content_obj = $pointing_node->get_content($lg);
        $content_obj->load_fields();
        $smarty->assign('pointing', $content_obj);
    }
    
    return $content;
}


// This is very similar to the Nodesort class and should probably be merged with it
class Compare_Pointings {
    static function by_field($field='title') {
        $cmp = new self();
        $cmp->field = $field;
        return array($cmp, 'cmp');
    }
    
    function cmp($node1, $node2) {
        $fieldname = $this->field;
        
        $c1 = $node1->get_content();
        $c2 = $node2->get_content();
        
        $order = 0;
        if($c1 && $c2)
        {
            $c1->load_fields();
            $c2->load_fields();
            $c1_set = isset($c1->$fieldname);
            $c2_set = isset($c2->$fieldname);
            if ($c1_set && $c2_set) {
                // If both values are numeric we sort numerically else we use alphabetical order
                if (is_numeric($c1->$fieldname) && is_numeric($c2->$fieldname)) {
                    $order = $c1->$fieldname - $c2->$fieldname;
                } else {
                    $order = strcasecmp($c1->$fieldname, $c2->$fieldname);
                }
            } else {
                // Empty fields go first
                $order = intval($c1_set) - intval($c2_set);
            }
        }
        return $order;
    }
}
