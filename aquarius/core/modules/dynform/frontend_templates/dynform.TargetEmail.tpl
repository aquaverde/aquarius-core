<div class="formInput">
    <select id="{$field.id}" name="{$field.id}"
            {if $field.required}class="require_pulldown"{/if}
            size="1"
    >
        <option value="0">{wording please_choose}</option>
        {foreach from=$options key=email item=option}
            <option value="{$email|escape}">{$option|escape}</option>
        {/foreach}
    </select>
</div>
