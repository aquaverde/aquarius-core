<span class="field checkbox">
    {foreach from=$field.options key=index item=option}
        <label for="{$field.id}{$index}">
            <input type="checkbox" id="{$field.id}{$index}" name="{$field.id}[]" value="{$option|escape}"{if $field.required} required{/if}>
            {$option|escape}
        </label>
    {/foreach}
</span>