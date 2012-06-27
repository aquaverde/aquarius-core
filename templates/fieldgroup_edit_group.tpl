{include file='header.tpl'}
<h1>Field Group {$group->name|escape}</h1>
<form action="{url action=$lastaction}" method="post" style="display: inline">
    
    <div id="outer">
        <label>Name</label> <input type="text" name="name" value="{$group->name|escape}" class="ef"/>
        <label>Display Name</label> <input type="text" name="display_name" value="{$group->display_name|escape}" class="ef"/>
        <div class="inline-item">
            <label style="display: inline">Visibility</label>
            <select name="visibility_level">
                {html_options options=$visibility_levels selected=$group->visibility_level}
            </select>
        </div>
        <br/>
        <h2>Field selectors</h2>
        <div>Shell glob wildcards supported: *?[]</div>
        <table class="formadmin" width="100%" cellpadding="0" cellspacing="2" bgcolor="#ffffff">
    {foreach from=$selectors item=selector}
            <tr>
                <td><input type="text" name="selectors[]" value="{$selector->selector|escape}" /></td>
            </tr>
    {/foreach}
        </table>
  <input type="submit" name="save_group_{$lastaction->id}" value="{#s_done#}" class="submit"/>
</form>
<form action="{url}" method="post" style="display: inline">
  &nbsp;
  <input type="submit" value="{#s_cancel#}" class="cancel"/>
</form>
{include file='footer.tpl'}