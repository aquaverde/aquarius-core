{strip}
<div class="nodetree_row nodetree_node" style="clear:both">
    {if $entry.show_toggle}
         <a class="nodetree_toggle {if $entry.open}expand{else}contract{/if} {if $entry.open}open{/if}"></a>
    {/if}
    <div style="width: 15px; padding-top: 2px; display:inline">
        {if $entry.selectable}
            {if $multi}
                <input id="select_{$entry.node->id}" name="node_select" class="node_select" type="checkbox" data-title="{$entry.title|escape:htmlall:'UTF-8'}" {if $entry.selected}checked="checked"{/if} value='{$entry.node->id}'/>
            {else}
                <input id="select_{$entry.node->id}" name="node_select" class="node_select" type="radio" data-title="{$entry.title|escape:htmlall:'UTF-8'}" {if $entry.selected}checked="checked"{/if} value='{$entry.node->id}'/>
            {/if}
        {/if}
    </div>
    <label  style="display:inline" for="select_{$entry.node->id}" class="nodetree_title{if $entry.node->is_content()} nodetree_title_content{/if}">
        &nbsp;{if !$entry.node->active}<span style="color:red">{/if}{$entry.title}{if !$entry.node->active}</span>{/if}
    </label>
</div>
{if $entry.open}
    <ul class="nodetree_children">
    {foreach from=$entry.children item=childentry}
        <li class="nodetree_entry{if !$childentry@last} nodetree_connection{/if}">
            <div class="nodetree_container" {if $childentry.node->id} id="nodetree_entry_{$childentry.node->id}"{/if} data-node="{$childentry.node->id}">
                {include file="nodes_select_container.tpl" entry=$childentry hide_root=false}
            </div>
        </li>
    {/foreach}
    </ul>
{/if}
{/strip}