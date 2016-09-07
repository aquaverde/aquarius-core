{include file="header.tpl"}
<div class="topbar">
<div style="float: right;">
    <a href="{$attrs.publicpath|download}"><span class="glyphicon glyphicon-download gi-15x" title="{#s_download#}"></span></a></td>
    &nbsp;&nbsp;
    {if $prev}
        <a href="{url action=$prev}">
            <span class="glyphicon glyphicon-circle-arrow-left gi-15x" title="{#s_next#}"></span>
        </a>
    {else}
        &nbsp;&nbsp;&nbsp;&nbsp;
    {/if}
    <a href="{url}"><span class="glyphicon glyphicon-circle-arrow-up gi-15x" title="{#s_back#}"></span></a>
    {if $next}<a href="{url action=$next}"><span class="glyphicon glyphicon-circle-arrow-right gi-15x" title="{#s_prev#}"></span></a>{/if}
</div>
<div style="float: right; margin: 0 0.5em;">
{if $may_delete}
    {if $delete}
    <div>
    <form action="{url action=$next}" method="post" style="display: inline">
        {$smarty.config.s_confirm_delete_file|sprintf:$attrs.name}<br>
        <button type="submit" name="{$delete}" class="btn btn-default" title="{#s_delete#}">
            {#s_delete#}
        </button>
    </form>
    <form action="{url action=$lastaction}" method="post" style="display: inline">
        <button type="submit" class="btn btn-primary">
            {#s_cancel#}
        </button>
    </form>
    </div>
    {else}
    <a href="{url action=$lastaction}&delete=1">
        <span class="glyphicon glyphicon-trash gi-15x" tilte="{#s_delete#}"></span>
    </a>
    {/if}
{/if}
</div>
<table class="meta-file">
    <tr>
        <th>{#s_filename#}</th>
        <td>{$attrs.name|escape}</td>
    </tr>
    <tr>
        <th>{#s_filesize#}</th>
        <td>{$file->size('kB')}kB</td>
    </tr>
{if $attrs.type == "image"}
    <tr>
        <th>{#s_dimensions#}</th>
        <td>{$attrs.size.0}x{$attrs.size.1}px</td>
    </tr>
{/if}
</table>
<table class="meta-file">
    <tr>
{if $attrs.references}
        <th>{#s_references#}</th>
        <td>
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
        </td>
{/if}
    </tr>
</table>
<div class="clear"></div>
</div>

<div style="margin: 1em 0;">
{if $attrs.type == "image"}
    <a href="{$attrs.publicpath}" target="_blank">
        <img src="{resize image=$attrs.publicpath w=1000}" alt="{$attrs.name}" style="max-width: 100%; padding: 1em; border: inset;"/>
    </a>
{elseif $attrs.type == "pdf"}
    <iframe src="{$attrs.publicpath}" style="width: 100%; height: 80em">
{else}
    <a href="{$attrs.publicpath}" target="_blank">
        <img src="buttons/{$attrs.button}" alt="{$attrs.name}" title="{$attrs.name}"/>
    </a>
{/if}
{include file="footer.tpl"}