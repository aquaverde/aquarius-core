<label>
    <input type="radio" value="1" name="{$field.formname}" {if $field.value == "1" }checked="checked"{/if}/>&nbsp;{$field.formfield->sup3}&nbsp;
</label>
<label>
    <input type="radio" value="0" name="{$field.formname}" {if $field.value == "0" || $field.value == "" }checked="checked"{/if}/>&nbsp;{$field.formfield->sup4}&nbsp;
</label>