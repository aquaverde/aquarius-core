{path_parents}

<div id="path">
    {foreach from=$path_parents item=i name=my_path}
        {assign var="title" value=$i.title|truncate:50}
        {if $i.action}
            <a href="{url action0=$lastaction action1=$i.action}" title="{$i.title} {#s_edit#}">{$title|strip_tags}</a>
        {else}
            {$title}
        {/if}
        {if not $smarty.foreach.my_path.last} | {/if}
    {/foreach}
</div>