<?php
/** @package Aquarius.frontend */

/** Build a list of nodes and assign to variable.
  * Only nodes where content for the target language exists
  * will be included in the list. Inactive nodes or nodes with inactive content
  * will not be included in the list. This may be overridden for preview.
  * 
  *  @param nodes Comma separated list of node ids to be included in the list. Preset: empty.
  *  @param childrenof Include children of given node in list (deprecated name 'childsof' supported as well)
  *  @param siblingsof Include children of parent node in list. Preset: no.
  *  @param hide comma-separated list of nodes never to be included in list. Preset: empty.
  *  @param lg target language code. PReset: current language.
  *  @param active Specify which node is active. Preset: current node
  *  @param shuffle Randomizes the list if set to true, preset: no
  *  @param limit cuts the list after the given limit, preset: false, signifying ∞
  *  @param start start at this index (ignore start of list), preset: 0
  *  @param var name of the smarty variable to assign the list to
  *  @param menu don't list nodes which should not be shown in menus, preset: false (DEPRECATED name honor_show_in_menu)
  *  @param include_inactive show nodes even if their parents are inactive, default false
  *  @param return_list Used internally to flag that the list should be returned as is
  *  @param filter filter sentence selecting nodes that will end up in the list
  *
  * Should none of the parameters nodes, childrenof, nor siblingsof be specified,
  * the list will be empty.
  * 
  * The list of nodes will be assigned to 'var'. Each item will be a dictionary with the following entries:
  * <pre>
  *    content: content for node of this entry
  *    node: the node
  *    active: Set to true for all parents (and itself) of the currently active node
  *    next_active: true if next entry will be active (DEPRECATED because unused)
  *    previous_active: true if previous entry was active (DEPRECATED because unused)
  *    first: set to true for the first entry
  *    last: set to true for the last entry
  *    index: the index, starts with 1 (not 0)
  * </pre>

Example:
<code>
{loadnodes childrenof=$node active=$node var=subnodes}
{if $subnodes|@count}
    <ul>
    {foreach from=$subnodes item=subnode}
        <li{if $subnode.active} id="NavigRightOn"{/if}>
            {link node=$subnode.node}{$subnode.content->title}{/link}
        </li>
    {/foreach}
    </ul>
{else}
    No subnodes here, move along.
{/if}
</code>
*/
function smarty_function_loadnodes($params, $smarty) {
    $nodes = db_Node::get_nodes(get($params, 'nodes'));
    $hide = get($params, 'hide');
    $hidenodes = db_Node::get_nodes($hide);
    $childrenof = db_Node::get_node(get($params, 'childrenof'));
    if (!$childrenof) $childrenof = db_Node::get_node(get($params, 'childsof'));
    $siblingsof = db_Node::get_node(get($params, 'siblingsof'));
    $lg = get($params, 'lg', $smarty->get_template_vars('lg'));
    $menu = (bool)get($params, 'menu', get($params, 'honor_show_in_menu', false));
    $custom_filter_sentence = get($params, 'filter');

    $list = array();

    $activenodes = array();
    if (isset($params['active'])) {
        $active = db_Node::get_node($params['active']);
    } else {
        $active = $smarty->get_template_vars('node');
    }
    if ($active) {
        foreach($active->get_parents(true) as $parent) {
            $activenodes[] = $parent->id;
        }
    }

    $hideids = array();
    foreach($hidenodes as $hidenode) {
        $hideids[] = $hidenode->id;
    }

    $filters = array(
        NodeFilter::create('not', NodeFilter::create('ids', $hideids)),
        new NodeFilter_Login_Required($smarty->get_template_vars("user"), true)
    );

    // Do not show inactive nodes
    $prefilter = array();
    if ($smarty->require_active) {
        $filters []= NodeFilter::create('has_content', $lg);
        if (get($params, 'include_inactive', false)) {
            $prefilter = array('inactive_self');
            $filters []= NodeFilter::create('active_self', $lg);
        } else {
            $prefilter = array('inactive');
            $filters []= NodeFilter::create('active', $lg);
        }
    }

    if ($menu) {
        $filters[] = NodeFilter::create('show_in_menu', true) ;
    }

    if ($custom_filter_sentence) {
        $custom_filter = false;
        try {
            global $aquarius;
            $custom_filter = $aquarius->filterparser->interpret($custom_filter_sentence, $smarty->get_template_vars());
        } catch (FilterParsingException $filter_error) {
            $smarty->trigger_error("Failed parsing filter '$custom_filter_sentence': ".$filter_error->getMessage());
        }
        if ($custom_filter) $filters []= $custom_filter;
    }

    $filter = NodeFilter::create('and', $filters);

    // push nodes given by id
    foreach($nodes as $node) {
        if ($filter->pass($node)) $list[] = $node;
    }

    // push children of a node
    if ($childrenof) {
        $list = array_merge($list, $childrenof->children($prefilter, $filter));
    }

    // push siblings of a node
    if ($siblingsof) {
        $parent = $siblingsof->get_parent();
        if ($parent) {
            $list = array_merge($list, $parent->children($prefilter, $filter));
        } else {
            $list[] = $siblingsof; // Lone root
        }
    }

    // Randomize the list if this was requested
    if (get($params, 'shuffle')) shuffle($list);

    // Start at
    $start = get($params, 'start');
    if (is_numeric($start)) {
        $list = array_slice($list, $start);
    }

    // Limit length
    $limit = get($params, 'limit');
    if (is_numeric($limit)) {
        $list = array_slice($list, 0, $limit);
    }
    
    $items = array();
    foreach ($list as $index => $node) {
        $content = $node->get_content($lg);
        if (!$content) continue;
        
        $content->load_fields();
        $items[] = array(
            'content'         => $content,
            'node'            => $node,
            'active'          => in_array($node->id, $activenodes),
            'next_active'     => isset($list[$index + 1]) && in_array($list[$index + 1]->id, $activenodes),
            'previous_active' => isset($list[$index - 1]) && in_array($list[$index - 1]->id, $activenodes),
            'first'           => $index == 0,
            'last'            => $index >= count($list) - 1,
            'index'           => $index + 1
        );
    }
    
    if (get($params, 'return_list')) {
        return $items;
    } else {
        $name = get($params, 'var');
        $smarty->assign($name, $items);
    }
}
