{include file='header.tpl'}
<h1>Group Selection</h1>
<div id="outer">
<form action="{url action=$lastaction}" method="post" style="display: inline">
    
        <label>Name <input type="text" name="name" value="{$selection->name|escape}" class="form-control"/></label>
        {foreach from=$groups item=group}
            <label for="group_{$group->fieldgroup_id}">
                <input type="checkbox" name="selected[]" value="{$group->fieldgroup_id}" id="group_{$group->fieldgroup_id}" {if $group->fieldgroup_id|in_array:$selected_groups}checked="checked"{/if}/> {$group->name|escape}
            </label>
        {/foreach}
        </table>
        <button type="submit" name="save_selection_{$lastaction->id}" value="{#s_done#}" class="btn btn-primary">{#s_done#}</button>
        <button type="submit" name="make_standard_{$lastaction->id}" value="Make this the standard selection" class="btn btn-default">Make this the standard selection</button>
</form>
<form action="{url}" method="post" style="display: inline">
    &nbsp;
    <button type="submit" value="{#s_cancel#}" class="btn btn-default">{#s_cancel#}</button>
</form>
</div>
{include file='footer.tpl'}