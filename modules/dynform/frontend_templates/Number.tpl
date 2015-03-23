{if $field.options}<span class="text">{$field.options|escape}</span>{/if}
<span class="field number"> 
    <input type="number" name="{$field.id}" id="{$field.id}" class="number" value=""{if $field.width} style="width:{$field.width * 10}px" maxlength="{$field.width}"{/if} placeholder="{$field.title|escape}{if $field.required}&nbsp;*{/if}"{if $field.required} required{/if}>
</span>