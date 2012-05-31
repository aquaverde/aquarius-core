{if $files}
    <option value="">{#s_choose#}</option>
    <optgroup label="----">
        <option value="">{#s_no_picture#}</option>
    </optgroup>
    <optgroup label="----">
    {foreach from=$files item=file}
        <option value="{$file|escape}"{if $selected == $file} selected="selected"{/if}>{$file|truncate:60:"…":true:true|escape}</option>
    {/foreach}
    </optgroup>
{else}
    <option value="">{#s_choose_no_files#}</option>
{/if}