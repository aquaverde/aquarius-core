<span class="field targetEmail"> 
    <select id="{$field.id}" name="{$field.id}"{if $field.required} required{/if}>
    	<option value="">{$field.title|escape}{if $field.required}&nbsp;*{/if}</option>
    {foreach from=$options key=email item=option}
        <option value="{$email|escape}">{$option|escape}</option>
    {/foreach}
    </select>
</span>