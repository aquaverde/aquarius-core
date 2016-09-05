{include file="header.tpl"}

<div class="bigbox">
<div style="float: right">
    {include file="filemgr_thumbnail.tpl" fileinfo=$attrs}
</div>
{#s_filename#}: "{$attrs.name|escape}"<br>
{if $attrs.type == "image"}{#s_dimensions#}: {$attrs.size.0}x{$attrs.size.1}px<br>{else}&nbsp;{/if}
{#s_filesize#}: {$file->size('kB')}&nbsp;kB<br>
<a href="{$attrs.publicpath|download}"><span class="glyphicon glyphicon-download"></span> Download</a><br>

{if $attrs.references}
<h3>{#s_references#}</h3>
<div class="ref_cell">
    {foreach from=$attrs.references item=content}
        {assign var="editaction" value="contentedit:edit:`$content->node_id`:`$content->lg`"|makeaction}
        {if $editaction}
            <a href="{url action0=$editaction action1=$lastaction}" class="little">&gt;
        {/if}
            {$content->cache_title|strip_tags|truncate:50}{if $lg != $content->lg} ({$content->lg}){/if}
        {if $editaction}
            </a>
        {/if}
        {if $content@last}{else}<br/>{/if}
    {/foreach}
</div>
{/if}

{if $may_delete}
    {if $delete}
    <form action="{url action=$next}" method="post" style="display: inline">
        Confirm deleting file "{$attrs.name}":
        <button type="submit" name="{$delete}" class="btn btn-default">
            {#s_delete#}
        </button>
    </form>
    <form action="{url action=$lastaction}" method="post" style="display: inline">
        <button type="submit" class="btn btn-primary">
            {#s_cancel#}
        </button>
    </form>
    {else}
    <form action="{url action=$lastaction}" method="post">
        <button type="submit" name="delete" value="1" class="btn btn-primary">
            {#s_delete#}...
        </button>
    </form>
    {/if}
    <br>
{/if}

{if $prev}<a href="{url action=$prev}">&lt;&lt;</a>{/if}
<a href="{url}">{#s_close#}</a>
{if $next}<a href="{url action=$next}">&gt;&gt;</a>{/if}

</div>