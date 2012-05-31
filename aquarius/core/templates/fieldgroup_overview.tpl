{include file='header.tpl'}
<h1>{$lastaction->get_title()}</h1>

<div class="bigbox">
  <form action="{url action=$lastaction}" method="POST">
    <h2>Group Selections</h2>
    <table border="0" cellpadding="0" cellspacing="0" class="table2">
        <tr>
            <th>Selection name</th>
            <th>Included groups</th>
        </tr>
    {whilefetch object=$selection}
        <tr class="{cycle values="even,odd"}">
            <td>
                {if $selection->is_standard}<strong>{/if}
                {action action="fieldgroup:edit_selection:`$selection->fieldgroup_selection_id`"}
                    <a href={url action1=$lastaction action2=$action}>
                            {$selection->name}
                    </a>
                    {actionlink action=$action show_title=0}
                {/action}
                {if $selection->is_standard}</strong>{/if}
            </td>
            <td>
                {foreach from=$selection->selected_groups() item=group name=group}
                    {$group->name}{if !$smarty.foreach.group.last},{/if}
                {/foreach}
            </td>
            <td align="right">
                {actionlink action="fieldgroup:delete_selection:`$selection->fieldgroup_selection_id`" show_title=0}&nbsp;&nbsp;
            </td>
        </tr>
    {/whilefetch}
    </table>
    {actionlink action=$new_selection new_button=1}
</div>

<br/>

<div class="bigbox">
    <h2>Field Groups</h2>
    <table border="0" cellpadding="0" cellspacing="0" class="table2">
        <tr><th>Group name</th><th>Display Name</th><th>Field selections</th><th></th></tr>
    {whilefetch object=$fieldgroup}
        <tr class="{cycle values="even,odd"}">
            <td>
                {action action="fieldgroup:edit_group:`$fieldgroup->fieldgroup_id`"}
                    <a href="{url action0=$lastaction action1=$action}">
                        {$fieldgroup->name}
                    </a>
                    {actionlink action=$action show_title=0}
                {/action}
            </td>
            <td>
                {$fieldgroup->title()|escape}
            </td>
            <td>
                {foreach from=$fieldgroup->field_selectors() item=field name=field}
                    {$field->selector|truncate:15|escape}{if !$smarty.foreach.field.last},{/if}
                {/foreach}
            </td>
            <td>
                {actionlink action="fieldgroup:move_group:`$fieldgroup->fieldgroup_id`:up" show_title=0}
                {actionlink action="fieldgroup:move_group:`$fieldgroup->fieldgroup_id`:down" show_title=0}&nbsp;&nbsp;
            </td>
            <td>
                {actionlink action="fieldgroup:delete_group:`$fieldgroup->fieldgroup_id`" show_title=0}&nbsp;&nbsp;
            </td>
        </tr>
    {/whilefetch}
    </table>
    {actionlink action=$new_fieldgroup new_button=1}
  </form>
</div>
{include file='footer.tpl'}