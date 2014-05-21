{include file='header.tpl'}
<h1>{#fe_address_edit#}</h1>

<div id="outer">
  <form action="{url}" method="POST">
    {foreach from=$form_data.elements item=form_field}
        {$form_field.html}
    {/foreach}

    {foreach from=$form_data.sections.0.elements item=form_field }
        {strip}
        {if $form_field.type != "submit"}
            <label for="{$form_field.name}"{if $form_field.error} style="color: red"{/if}>
                {$form_field.label}
            </label>

            <div {if $form_field.error} style="border: 1px solid red;"{/if}>
                {$form_field.html}
            </div>
        {/if}
        {/strip}
    {/foreach}

    <input name="insert" value="{$insert}" type="hidden"/>
    {include file="select_buttons.tpl"}
  </form>
</div>
{include file='footer.tpl'}