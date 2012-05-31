{include file='header.tpl'}
<h1>{#tableexport_edit#}</h1>

<div id="outer">
  
  <form {$form_data.attributes}>
    {foreach from=$form_data.elements item=form_field}
        {$form_field.html}
    {/foreach}
    <!-- Display the fields -->
    {foreach from=$form_data.sections.0.elements item=form_field }
        {if $form_field.type != "submit"}

            <div class="tableexport_edit">
            <label for="{$field.htmlid}" title="{$field.formfield->name}"
            {if $form_field.required && $form_field.value == ""} 
                class="inline errorlabel"
            {/if}>
                {$form_field.label}
            </label>
            </div>

            <div class="tableexport_edit">
            {if $form_field.required && $form_field.value == ""} 
                <div id="errorfield">
                {$form_field.html}
                </div>
            {else}
                <div class="efcontainer">
                {$form_field.html}
                </div>
            {/if}
            </div>

        {/if}

    {/foreach}

    <input name="__submit__" value="{#s_done#}" type="submit" class="submit"/>
    <input name="cancel" value="{#s_cancel#}" type="submit" class="cancel"/>

  </form>


</div>  
{include file='footer.tpl'}