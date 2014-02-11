<div class="formInput">
    <select id="{$field.id}" name="{$field.id}"
            {if $field.required}class="require_pulldown"{/if}
            size="1"
    >
        <option value="">{wording please_choose}</option>
        {foreach from=$field.options key=index item=label}
            <option
                value="{$index|escape}"
                {if $field.options|@count == 1}selected="selected"{/if}
            >{$label|escape}</option>
        {/foreach}
    </select>
</div>
