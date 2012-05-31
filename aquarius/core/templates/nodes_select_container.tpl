{strip}
<div class="nodetree_row {if !$entry.node->newaction}nodetree_node{/if}" style="clear:both">
    {if $entry.show_toggle}
        {if $entry.open}
            <img class="nodetree_toggle" src="picts/toggle-open.gif" onclick="nodetree.update({$entry.node->id}, {ldelim}open: 0, selected: selected_ids(){rdelim})" alt="{#s_close#}"
            />
        {else}
            <img class="nodetree_toggle" src="picts/toggle-closed.gif" onclick="nodetree.update({$entry.node->id}, {ldelim}open: 1, selected: selected_ids(){rdelim})" alt="{#s_open#}"
            />
        {/if}
    {/if}
    <div style="width: 15px; padding-top: 2px; display:inline">
        {if $entry.selectable}
            {if $multi}
                <input id="select_{$entry.node->id}" name="node_selection" type="checkbox" onclick='changed_multi_selection({$entry.node->id}, "{$entry.title|addslashes|escape:htmlall:'UTF-8'}", this.checked)' {if $entry.selected}checked="checked"{/if}/>
            {else}
                <input id="select_{$entry.node->id}" name="node_selection" type="radio" onclick='changed_selection({$entry.node->id}, "{$entry.title|addslashes|escape:htmlall:'UTF-8'}")' {if $entry.selected}checked="checked"{/if}/>
            {/if}
        {/if}
    </div>
    <label  style="display:inline" for="select_{$entry.node->id}" class="nodetree_title{if $entry.node->is_content()} nodetree_title_content{/if}">
        &nbsp;{$entry.title}
    </label>


</div>
{if $entry.open}
    <ul class="nodetree_children">
    {foreach from=$entry.children item=childentry}
        <li class="nodetree_entry{if !$childentry.last} nodetree_connection{/if}">
        {if $childentry.last}
            <img class="nodetree_connection" src="picts/joinbottom.gif" alt="" />
        {else}
            <img class="nodetree_connection" src="picts/join.gif" alt="" />
        {/if}
            <div class="nodetree_container" {if $childentry.node->id} id="nodetree_entry_{$childentry.node->id}"{/if}>
                {include file="nodes_select_container.tpl" entry=$childentry hide_root=false}
            </div>
        </li>
    {/foreach}
    </ul>
{/if}
{/strip}