<?
/** List pushed childs of some node.
  * Params:
  *   childsof: Look for pushed content in the descendants of this node
  *   field: Field that must be set (default: push)
  *   sort: sort by content_field
  *   limit: Maximum count of elements to show
  *   reverse: reverse sorting order (applied after limiting result)
  *   shuffle: randomise results, only if no sort param defined
  *
  * Warning: confusion will ensue if {pushed} blocks are nested!
  */

function smarty_block_pushed($params, $content, &$smarty, &$repeat) {
    
    static $list;
    // On first invocation get elements for list
    if ($repeat) {
        $node = db_Node::get_node(get($params, 'childsof'));
        if (!$node) throw new Exception("Childsof node invalid, got ".get($params, 'childsof'));
        $limit = intval(get($params, 'limit'));
        $pushed = mysql_real_escape_string(get($params, 'field', 'push'));
        $sort = mysql_real_escape_string(get($params, 'sort', false));
        $lg = mysql_real_escape_string($smarty->get_template_vars('lg'));

        // Don't we all like complex SQL? I sure do :-) (though it turned ugly with the content_field_value table)
        // In essence, we're looking for content that has a field $pushed = 1, and we want to retrieve the content ordered after another content field. Thus we have to join in the content_field (and content_field_value) table twice, first for detecting a pushed content (aliased field_pushed) and second to order by the $sort field (aliased field_order).
        $query = "
            SELECT DISTINCT content.id"; 
        if ($sort) $query .= ", field_order_value.value as order_value " ;
        $query .= "
            FROM
                   node
              JOIN content ON content.node_id = node.id
              JOIN content_field field_pushed ON field_pushed.content_id = content.id
              JOIN content_field_value field_pushed_value ON field_pushed_value.content_field_id = field_pushed.id";
        if ($sort) $query .= "
              JOIN content_field field_order ON field_order.content_id = content.id
              JOIN content_field_value field_order_value ON field_order_value.content_field_id = field_order.id" ;      
        $query .= "      
            WHERE
                  node.cache_left_index > '$node->cache_left_index'
              AND node.cache_right_index < '$node->cache_right_index'
              AND content.lg = '$lg'
              AND field_pushed.name = '$pushed'
              AND field_pushed_value.value = 1" ;
        if ($sort) $query .= "
              AND field_order.name = '$sort'" ;
        $query .= "
              AND node.active = 1
              AND content.active = 1
            ORDER BY " ;
        
        if ($sort)                       $query .= 'order_value' ;
        elseif (get($params,'shuffle'))  $query .= 'RAND()' ;
        else                             $query .= 'node.weight' ;            
        
        if ($limit > 0) $query .= "
            LIMIT 0,$limit";
            
        $list = $GLOBALS['DB']->listquery($query);
        if (get($params, 'reverse')) $list = array_reverse($list);
    }
    
    // Next node on the list
    $content_id = array_shift($list);
   
    // Repeat if there's another node
    $repeat = (bool)$content_id;
    if ($repeat) {
        $content_obj = DB_DataObject::staticGet('db_Content', $content_id);
        $content_obj->load_fields();
        $smarty->assign('entry', $content_obj);
        $smarty->assign('lastentry', empty($list));
    }
    return $content;
}
?>