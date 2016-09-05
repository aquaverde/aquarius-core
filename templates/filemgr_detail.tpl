{include file="header.tpl"}
<div class="topbar">
<div style="float: right">
    {if $prev}<a href="{url action=$prev}"><span class="glyphicon glyphicon-circle-arrow-left"></span></a>{/if}
    <a href="{url}"><span class="glyphicon glyphicon-circle-arrow-up"></span></a>
    {if $next}<a href="{url action=$next}"><span class="glyphicon glyphicon-circle-arrow-right"></span></a>{/if}
</div>
<table>
    <tr>
        <th>{#s_filename#}</th>
        <td>{$attrs.name|escape}</td>
        <td><a href="{$attrs.publicpath|download}"><span class="glyphicon glyphicon-download"></span> Download</a></td>
    </tr>
    <tr>
        <th>{#s_filesize#}</th>
        <td>{$file->size('kB')}kB</td>
    <tr>
{if $attrs.type == "image"}
    <tr>
        <th>{#s_dimensions#}</th>
        <td>{$attrs.size.0}x{$attrs.size.1}px</td>
    </tr>
{/if}
<table>
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


</div>

{if $attrs.type == "image"}
<div style="padding: 1em; border: inset;">
    <img src="{resize image=$attrs.publicpath w=1000}" alt="{$attrs.name}" style="max-width: 100%"/>
</div>
{elseif $attrs.type == "pdf"}
    <iframe src="{$attrs.publicpath}" style="width: 100%; height: 80em">
{else}
    <img src="buttons/{$attrs.button}" alt="{$attrs.name}" title="{$attrs.name}"/></a>
{/if}
{include file="footer.tpl"}