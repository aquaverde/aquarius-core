{if $field.options}
    <span class="formText">{$field.options|escape}</span>
{/if}
<div class="formInput">
    <input type='text' name='{$field.id}' id='{$field.id}' 
        class='checkfield {$field.classstr}' value=''
        {if $field.width}style='width:{$field.width * 10}px' maxlength='{$field.width}' {/if}
    />
</div>
