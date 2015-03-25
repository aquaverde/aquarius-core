{include file='header.tpl'}
<h1>{$title}</h1>
<form action="{url action=$saveaction}" method="post" style="display: inline">
    <div id="outer">
    <ul>
    {foreach from=$forms item=form}
        <li>
        {if $radios}
            {if !$checkboxes}<label>{/if}
            <input type='radio' name='selected_form' value='{$form.id}' {if $form.selected}checked{/if}>
        {/if}
        {if $checkboxes}
        <label style="display: inline">
            <input type='checkbox' name='checked_forms[]' value='{$form.id}'{if $form.checked}checked{/if}>
        {/if}
            &nbsp;{$form.title}{if $form.template} ({$form.template}){/if}
        </label>
        </li>
    {/foreach}
    </ul>
    </div>
    {if $reset_option}<label><input type='checkbox' name='reset' value='of course'> Reset overrides</label>{/if}
    <input type="submit" name="" value="{#s_done#}" class="btn btn-primary"/>
    <input type="submit" name="{$lastaction}" value="{#s_save#}" class="btn btn-default"/>
</form>
<form action="{url}" method="post" style="display: inline">
  &nbsp;
  <input type="submit" value="{#s_cancel#}" class="btn btn-default"/>
</form>
{include file='footer.tpl'}