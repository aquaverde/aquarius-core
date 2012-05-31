<div class="formInput checkbox">
    {foreach from=$field.options key=index item=option}
        <label for='{$field.id}_{$index}'>
            <input type='checkbox' id='{$field.id}_{$index}' name='{$field.id}[]' {if $field.required} class="require_checkbox" {/if}value="{$option|escape}" />
        &nbsp;{$option|escape}</label>
    {/foreach}
</div>