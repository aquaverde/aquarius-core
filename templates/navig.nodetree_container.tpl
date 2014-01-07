<div class="nodetree_row {if $tree.open}open{/if}">
    {if $tree.show_toggle}
            <img class="nodetree_toggle" id="nodetree_toggle_{$tree.node->id}" src="picts/lapse-{if $tree.open}open{else}closed{/if}.gif" onclick="this.src='picts/lapse-loading.gif'; nodetree.update({$tree.node->id}, {ldelim} open: {if $tree.open}0{else}1{/if} {rdelim})" alt="{if $tree.open}{#s_close#}{else}{#s_open#}{/if}"/>
    {/if}
    {assign var="nodetitle" value=$tree.node->get_contenttitle()|strip_tags}
    {assign var="action" value="contentedit:edit:`$tree.node->id`:$lg"|makeaction}
    {if $action}<a href="{url url=$adminurl action=$action action2=$lastaction}" title="{$nodetitle}: {#s_edit#} ({$tree.node->id})">{/if}
    <span class="glyphicon glyphicon-file{if !$tree.node->active} off{/if}"></span>
    <span class="nodetree_title{if $tree.node->is_content()} nodetree_title_content{/if}{if !$tree.node->get_content($lg)} dim{/if}">
        {$nodetitle|truncate:38}
        {if $tree.node->access_restricted == 1} 
            &nbsp;<span class="glyphicon glyphicon-lock" title="{#s_access_restricted#}"></span>
        {/if}
    </span>
    {if $action}</a>{/if}
{if $tree.children|@count > 0}
    <ul>
        {foreach item="child_tree" from=$tree.children}
            <li class="nodetree_entry{if !$child_tree.last} nodetree_connection{/if}">
                {if $child_tree.last}
                    <img class="nodetree_connection join_bottom" src="picts/joinbottom.gif" alt="" />
                {else}
                    <img class="nodetree_connection" src="picts/join.gif" alt="" />
                {/if}
                <div class="nodetree_container" id="nodetree_entry_{$child_tree.node->id}">
                    {include file="navig.nodetree_container.tpl" tree=$child_tree}
                </div>
            </li>
        {/foreach}
    </ul>
{/if}
</div>