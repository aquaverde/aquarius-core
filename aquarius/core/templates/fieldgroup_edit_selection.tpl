{include file='header.tpl'}
<h1>Group Selection</h1>
<form action="{url action=$lastaction}" method="post" style="display: inline">
    <div id="outer">
        <label>Name</label> <input type="text" name="name" value="{$selection->name|escape}" class="ef"/><br/><br/>
    {foreach from=$groups item=group}
<input type="checkbox" name="selected[]" value="{$group->fieldgroup_id}" id="group_{$group->fieldgroup_id}" {if $group->fieldgroup_id|in_array:$selected_groups}checked="checked"{/if}/><label style="display: inline" for="group_{$group->fieldgroup_id}">&nbsp;{$group->name|escape}</label><br/>
    {/foreach}
        </table>
  <input type="submit" name="save_selection_{$lastaction->id}" value="{#s_done#}" class="submit"/>
  <input type="submit" name="make_standard_{$lastaction->id}" value="Make this the standard selection" class="submit"/>
</form>
<form action="{url}" method="post" style="display: inline">
  &nbsp;
  <input type="submit" value="{#s_cancel#}" class="cancel"/>
</form>
{include file='footer.tpl'}