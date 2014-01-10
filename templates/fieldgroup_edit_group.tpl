{include file='header.tpl'}
<h1>Field Group {$group->name|escape}</h1>
<form action="{url action=$lastaction}" method="post" style="display: inline">
    
    <div id="outer">
        <label>Name</label> <input type="text" name="name" value="{$group->name|escape}" class="form-control"/>
        <label>Display Name</label> <input type="text" name="display_name" value="{$group->display_name|escape}" class="form-control"/>
        <div class="inline-item">
            <label style="display: inline">Visibility</label>
            <select name="visibility_level">
                {html_options options=$visibility_levels selected=$group->visibility_level}
            </select>
        </div>
        <br/>
        <h2>Field selectors</h2>
        <p>Shell glob wildcards supported: *?[]</p>
        <table class="table">
        {foreach from=$selectors item=selector}
                <tr>
                    <td><input type="text" name="selectors[]" value="{$selector->selector|escape}" class="form-control" /></td>
                </tr>
        {/foreach}
        </table>
        <button type="submit" name="save_group_{$lastaction->id}" value="{#s_done#}" class="btn btn-primary">{#s_done#}</button>
</form>
<form action="{url}" method="post" style="display: inline">
  &nbsp;
  <button type="submit" value="{#s_cancel#}" class="btn btn-default">{#s_cancel#}</button>
</form>
{include file='footer.tpl'}