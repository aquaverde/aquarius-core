{if $field.options}
    <span class="formText">{$field.options|escape}</span>
{/if}
<div class="formInput">
    <textarea name='{$field.id}' id='{$field.id}' class='checkfield {if $field.required} require_text{/if}' cols='70' rows='{$field.num_lines|default:2}'></textarea>
</div>