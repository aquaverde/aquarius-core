<<<<<<< HEAD
{if $field.options}<span class="text">{$field.options|escape}</span>{/if}
<span class="field email"> 
    <input type="email" name="{$field.id}" id="{$field.id}" class="email" value=""{if $field.width} style="width:{$field.width * 10}px" maxlength="{$field.width}"{/if} placeholder="{$field.title|escape}{if $field.required}&nbsp;*{/if}"{if $field.required} required{/if}>
</span>
=======
{if $field.options}
    <span class="formText">{$field.options|escape}</span>
{/if}
<div class="formInput">
    <input type='text' name='{$field.id}' id='{$field.id}' 
        class='checkfield {$field.classstr}'
        value='' maxlength='{$field->width}'
    />
</div>
>>>>>>> 5a76f5a2240b4abef17d02605ae78232f5c21408
