{foreach from=$nodelist item=nodeinfo name="for_node"}

    {*ajax action*}
    {assign var='listchildrenaction' value="nodetree:leftnavig_children:`$nodeinfo.node->id`:$lg"|makeaction}

    {assign var=next_index value=$smarty.foreach.for_node.index+1}
    {assign var=next_depth value=$nodelist[$next_index].node->cache_depth}
    
    {if $smarty.foreach.for_node.first}
        {assign var="nodeDepth" value=$nodeinfo.node->cache_depth}
    {/if}

    {if $nodeinfo.node->cache_depth <= $nodeDepth+1 and not $smarty.foreach.for_node.first}

    {*action url to be found by javascript*}
    <input type="hidden" value="admin.php{url action=$listchildrenaction}" name="url:{$nodeinfo.node->id}" id="url:{$nodeinfo.node->id}" />
    
    {foreach from=$nodeinfo.connections item=con}
        {assign var=connection value=$con}
    {/foreach}	
    
    {action action="contentedit:edit:`$nodeinfo.node->id`:$lg" continue=true}
    
        {assign var="nodetitle" value=$nodeinfo.node->get_contenttitle()|strip_tags}
        
        
        <li id="entry_{$nodeinfo.node->id}" class="nodetree {if $connection=="join"}middle{else}end{/if} {if $next_depth>$nodeDepth+1} lapsible{/if}">
            
            {if $next_depth<=$nodeDepth+1}
            
                {if $connection == "join"}
                        <img src="picts/join_nav.gif" alt="" />
                {else}
                        <img src="picts/joinbottom_nav.gif" alt="" />
                {/if}
            
            {/if}

        {if $action}
                <a href="admin.php{url action=$action}" title="{#s_edit#}: {$nodetitle} ({$nodeinfo.node->id})">
                <img src="picts/{$nodeinfo.node->icon()}_nav.gif" title="{#s_edit#}: {$nodetitle}  ({$nodeinfo.node->id})" alt="{#s_edit#}: {$nodetitle} ({$nodeinfo.node->id})"/>
                <span class="{if !$nodeinfo.node->get_content($content->lg)} dim{/if}">{$nodetitle|strip_tags|truncate:30}</span></a>
        {else}
                <img src="picts/{$nodeinfo.node->icon()}_nav.gif" alt=""/>&nbsp;
                <span class="{if !$nodeinfo.node->get_content($content->lg)} dim{/if}">{$nodetitle|strip_tags|truncate:30}</span>
        {/if}
        
        </li>
    {/action}
    {/if}
    
{/foreach}