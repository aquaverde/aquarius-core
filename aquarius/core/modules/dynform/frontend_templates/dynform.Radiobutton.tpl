<div class="formInput radio">
    {foreach from=$field.options key=index item=option}
        <label for='{$field.id}_{$index}'>
            <input type="radio" id="{$field.id}_{$index}" name="{$field.id}" {if $field.required}class="require_radio" {/if}value="{$option|escape}" />
        &nbsp;{$option|escape}</label>
    {/foreach}
</div>