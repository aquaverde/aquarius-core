{if $field.options}<span class="text">{$field.options|escape}</span>{/if}
<span class="field textarea"> 
    <textarea name="{$field.id}" id="{$field.id}" class="textarea" cols="" rows="" placeholder="{$field.title|escape}{if $field.required}&nbsp;*{/if}"{if $field.required} required{/if}></textarea>
</span> 
