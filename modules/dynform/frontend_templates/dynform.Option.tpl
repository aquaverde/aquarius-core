<div class="formInput select">
    <select id="{$field.id}" name="{$field.id}" {if $field.required}class="require_pulldown"{/if} size="1">
    	<option value="0">{wording please_choose}</option>
    {foreach from=$field.options item=option}
        <option value="{$option|escape}">{$option|escape}</option>
    {/foreach}
    </select>
</div>