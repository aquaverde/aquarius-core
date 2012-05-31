<?
require_once "lib/NodeFilter.php";
require_once "lib/Compare.php";

/** Select nodes by filter query
  *
  * @param filter filter query
  *
  *
  * Example:
  *
  * {select filter="has menu_position top"}
  *   {link node=$item.node}{$item.content->title}{/link}
  * {/select}
  *
  */

function smarty_block_select($params, $content, &$smarty, &$repeat) {
    static $items;

    // On first invocation of the block, we build the list of nodes
    if ($repeat) {
        global $DB;
        $lg = db_Languages::ensure_code(get($params, 'lg', $smarty->get_template_vars('lg')));
        
        $filters = array(new NodeFilter_Active());
        
        $filter_sentence = get($params, 'filter');
        if ($filter_sentence) {
            $custom_filter = false;
            try {
                global $aquarius;
                $custom_filter = $aquarius->filterparser->interpret($filter_sentence);
            } catch (FilterParsingException $filter_error) {
                $smarty->trigger_error("Failed parsing filter '$filter_sentence': ".$filter_error->getMessage());
            }
            if ($custom_filter) $filters []= $custom_filter;
        }
        
        $sql_filter = new SQL_NodeFilter();
        $sql_filter->add_filter(new Filter_Logic_And($filters));
        
        $nodes = $sql_filter->run($DB, $lg);
        
        // Sort in tree order for now
        usort($nodes, ObjectCompare::by_field('cache_left_index', 'intcmp'));
        
        /* The below is Copypasta from the loadnodes plugin, let's see whether it generalizes */
        $active = $smarty->get_template_vars('node');
        $activenodes = array();
        foreach($active->get_parents(true) as $parent) {
            $activenodes[] = $parent->id;
        }
        $items = array();
        foreach ($nodes as $index => $node) {
            $content = $node->get_content($lg);
            $content->load_fields();
            $items[] = array(
                'content'         => $content,
                'node'            => $node,
                'active'          => in_array($node->id, $activenodes),
                'first'           => $index == 0,
                'last'            => $index >= count($list) - 1,
                'index'           => $index + 1
            );
        }
    }

    // In each iteration, we check whether there's a pointing node left to display, and load its content
    $item = array_shift($items);
    $repeat = (bool)$item;

    if ($repeat) {
        $smarty->assign('item', $item);
    }
    return $content;
}
?>