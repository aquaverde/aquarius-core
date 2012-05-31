{assign var=sup3 value=$field.formfield->sup3}
{foreach from=$sup3|sf_split key="key" item="value"}
    <label>
        <input type="radio" value="{$key}" name="{$field.formname}" 
        {if $field.value == $key }checked="checked"{/if}/>&nbsp;{$value}
    </label>
{/foreach}