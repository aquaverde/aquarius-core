<?php
/** @package Aquarius.frontend */

/** Block to iterate over a list of nodes. See plugin function loadnodes for parameter documentation.
The block content will be executed for every loaded node. On each iteration the item will be assigned to smarty var 'item', or the name named in 'itemvar' parameter.

The following template variables are changed as well, but their use is DEPRECATED:
<pre>
    entry (or 'var' name): Current content
    active: Set to true for all parents (and itself) of the node specified in the active parameter
    next_active: true if next entry will be active
    previous_active: true if previous entry was active
    last: set to true for the last entry
    index: the index, starts with 1 (not 0)
    loadcontent: Flag to enable or disable direct content loading for each item. If this is enabled, the 'title', 'text', 'picture', and other fields are assigned inside the block. If this parameter is not specified, the config value frontend/loadcontent is used, default is false (no automatic loading).
</pre>

Example:
<code>
<ul>
    {list siblingsof=$node active=$node limit=15}
        <li{if $item.active} id="NavigRightOn"{/if}>
            {link node=$item.node}{$item.content->title}{/link}
        </li>
    {/list}
</ul>
</code>

Insane example. Build a three level file download list where the third level file list shows only for the active subcategory. (Note that since aquarius has now support for multiple files per field, the files would likely be added directly to the subcategory, instead of creating subnodes. The example is insightful nonetheless.)
<code>
    {list childrenof=$node var=file_category loadcontent=true}
    	<h2>{$title}</h2>
    	{list childrenof=$file_category.node var=file_subcategory active=$smarty.get.active loadcontent=true}
            <li>
                <a href="{href node=$file_subcategory.node param="active=`$file_subcategory.node->id`"}">
                    {if $file_subcategory.active}
                        <img src="/interface/arrow-on.gif" alt="">
                        <strong>{$title}</strong>
                    {else}
                        <img src="/interface/arrow.gif" alt="">
                        {$title}
                    {/if}
                </a>
            {if $file_subcategory.active}
                <ul>
                    {list childrenof=$file_subcategory.node var=file_item loadcontent=true}
                        <li class="file"><a href="/download/{$file.file}" title="Download {$file.file|basename} ({$file.legend|escape})"><img src="/interface/picto_pdf.gif" alt="download"> {$title}</a></li>
                    {/list}
                </ul>
            {/if}
            </li>
    	{/list}
    {/list}
</code>
*/
function smarty_block_list($params, $content, $smarty, &$repeat) {
    static $replace_stack = array(); // Stack containing outer smarty template vars

    static $lists; // Holds the list of nodes to be shown; as a stack for nested invocations
    
    global $aquarius;
    $loadcontent = get($params, 'loadcontent', $aquarius->conf('frontend/loadcontent', false));

    if ($repeat) {
        /* Load the list of nodes */
        require_once $smarty->_get_plugin_filepath('function','loadnodes');
        $params['return_list'] = true;
        $lists[] = smarty_function_loadnodes($params, $smarty);
        if ($loadcontent) {
            $replace_stack []= $smarty->tpl_vars;
        }
    }
    
    // Get the current list
    $current_list = array_pop($lists);
    
    // Next node on the list
    $current_item = array_shift($current_list);
    
    // Repeat if there's another node
    $repeat = (bool)$current_item;


    // Change smarty template vars if there's another node to be listed
    if ($repeat) {
        $itemname = get($params, 'itemvar', 'item');
        if ($itemname) $smarty->assign($itemname, $current_item);
        
        $contentname = get($params, 'var', 'entry');
        if ($contentname) $smarty->assign($contentname, $current_item['content']);

        // Deprecated entries
        $smarty->assign('active',           $current_item['active']);
        $smarty->assign('next_active',      $current_item['next_active']);
        $smarty->assign('previous_active',  $current_item['previous_active']);
        $smarty->assign('first',            $current_item['first']);
        $smarty->assign('last',             $current_item['last']);
        $smarty->assign('index',            $current_item['index']) ;

        $lists[] = $current_list;
        
        // Directly assign content fields
        if ($loadcontent) {
            $smarty->assign($current_item['content']->get_fields());
        }
    } else {
        /* End of block */
        if ($loadcontent) {
            $smarty->tpl_vars = array_pop($replace_stack); // HACK back
        }
    }
    
    return $content;
}
