<span class="field option"> 
    <select id="{$field.id}" name="{$field.id}"{if $field.required} required{/if}>
    	<option value="">{$field.title|escape}{if $field.required}&nbsp;*{/if}</option>
        {foreach from=$field.options item=option}
            <option value="{$option|escape}">{$option|escape}</option>
        {/foreach}
    </select>
</span>