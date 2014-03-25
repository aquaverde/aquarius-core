<div class="nodetree_row">
    
    {if $tree.show_toggle}
        <a class="nodetree_toggle {if $tree.open}expand{else}contract{/if} {if $tree.open}open{/if}" id="nodetree_toggle_{$tree.node->id}" data-node="{$tree.node->id}"></a>
    {/if}
    
    {assign var="nodetitle" value=$tree.node->get_contenttitle()|strip_tags}
    {assign var="action" value="contentedit:edit:`$tree.node->id`:$lg"|makeaction}
    {if $action}<a href="{url url=$adminurl action=$action action2=$lastaction|default:false}" title="{$nodetitle}: {#s_edit#} ({$tree.node->id})">{/if}
    <span class="glyphicon glyphicon-file{if !$tree.node->active} off{/if}"></span>
    <span class="nodetree_title{if $tree.node->is_content()} nodetree_title_content{/if}{if !$tree.node->get_content($lg)} dim{/if}">
        {$nodetitle|truncate:38}
        {if $tree.node->access_restricted == 1} 
            &nbsp;<span class="glyphicon glyphicon-lock" title="{#s_access_restricted#}"></span>
        {/if}
    </span>
    {if $action}</a>{/if}
    
{if $tree.children|@count > 0}
    <ul class="{if $base}nodetree_root{else}nodetree_children{/if} left_nav{if $tree.children|@count < 2} single_element{/if}">
        {foreach item="child_tree" from=$tree.children name="child_tree"}
            <li class="nodetree_entry{if !$child_tree@last} nodetree_connection{/if} nodetree_container container_{$child_tree.node->id}" data-node="{$child_tree.node->id}">
                {include file="navig.nodetree_container.tpl" tree=$child_tree base=false}
            </li>
        {/foreach}
    </ul>
{/if}
    
</div>