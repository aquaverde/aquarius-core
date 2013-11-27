{include file='header.tpl'}
<h1>{#s_last_changes#}</h1>


<h2>{$journal_info|@count} {#s_last_changes#}</h2>
<table class="table table-hover">
{foreach from=$journal_info item=journal_item}
    {strip}
    {assign var='content' value=$journal_item.content}
    {assign var='node' value=$content->get_node()}
    <tr>
        <td>
            <img class="imagebutton" src="picts/{$node->icon()}.gif"/>&nbsp;
            {action action="contentedit:edit:`$node->id`:`$content->lg`" continue=true}
            {if $action}
                <a href="{url action0=$lastaction action1=$action}" title="edit">{$content->get_title()|strip_tags|truncate:80} ({$content->lg})</a>
            {else}
                {$content->get_title()|strip_tags|truncate:80} ({$content->lg})
             {/if}
            {/action}
        </td>
        <td>{$journal_item.user->name}</td>
        <td align="right"><i>{$journal_item.last_change|date_format:"%d.%m.%Y %H:%M"}</i>&nbsp;</td>
    </tr>
    {/strip}
{/foreach}
</table>

{include file='footer.tpl'}