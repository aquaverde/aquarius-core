<?
/** @package Aquarius.frontend */

/** Iterate over list of most recent updates of content
  *
  * Params:
  * <pre>
  *    subnodesof: Restrict list of last updates to section under given node
  *    lg: target language (default is the current lang)
  *    limit: cuts the list after the given limit (default 100, max 100)
  *    include_inactive: show nodes even if their parents are inactive, default false
  * </pre>
  * On each iteration, the smarty variable $item will be assigned new values. Each item will be a dictionary with the following entries:
  * <pre>
  *    content: updated content instance
  *    node: the node corresponding to the content
  *    last_update: Date of the last change
  *    first: set to true for the first entry
  *    last: set to true for the last entry
  *    index: the index, starts with 1 (not 0)
  * </pre>
  * Items are sorted by last_update, descending. So you get the latest change first.
*/
function smarty_block_lastupdates($params, $content, $smarty, &$repeat) {
    static $items;

    
    if ($repeat) {
        $limit = min(100, intval(get($params, 'limit', 100)));
        $subnodesof = get($params, 'subnodesof');
        $include_inactive = get($params, 'include_inactive');
        $lg = db_Languages::validate_code(get($params, 'lg', $smarty->get_template_vars('lg')));


        $conditions = array("content.lg = '$lg'");

        $root = false;
        if ($subnodesof) {
            $root = db_Node::get_node($subnodesof);
            if (!$root) throw new Exception("Unable to load node '$subnodesof'");
        }

        if ($root) {
            $conditions []= 'node.cache_left_index > '.$root->cache_left_index;
            $conditions []= 'node.cache_right_index < '.$root->cache_right_index;
        }

        $conditions []= 'content.active';
        if ($include_inactive) {
            $conditions []= 'node.active';
        } else {
            $conditions []= 'node.cache_active';
        }

        global $DB;
        $entries = $DB->query('
            SELECT content_id, MAX(journal.last_change) as last_change
            FROM journal
            JOIN content ON journal.content_id = content.id
            JOIN node ON node.id = content.node_id
            WHERE '.join(' AND ', $conditions).'
            GROUP BY content_id
            ORDER BY last_change DESC
            LIMIT '.$limit
        );

        $items = array();
        $first = false;
        $last = false;
        $index = 0;
        while($entry = mysql_fetch_assoc($entries)) {
            $content = DB_DataObject::factory('content');
            $found_content = $content->get($entry['content_id']);
            if ($found_content) {
                $content->load_fields();
                $node = $content->get_node();
                $last_update = $entry['last_change'];
                $index += 1;
                $items []= compact('content', 'node', 'last_update', 'first', 'last', 'index');
            }
        }
        if (!empty($items)) {
            $items[0]['first'] = true;
            $items[count($items)-1]['last'] = true;
        }
    }

    // Next node on the list
    $current_item = array_shift($items);
    
    // Repeat if there's another node
    $repeat = (bool)$current_item;

    // Change smarty template vars if there's another node to be listed
    if ($repeat) {
        $smarty->assign('item', $current_item);
    }
    
    return $content;
}
?>