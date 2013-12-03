{include file='header.tpl'}
<h1>{#field_rename#}</h1>

This tool can be used to rename content fields. Say you have a field 'stupid_name' in a form, and you'd like to rename that to 'smart_name'. First you will use this tool to copy fields, with original_name set to 'stupid_name' and new_name set to 'smart_name'. Now, as soon as you create a field 'smart_name', the content from field 'stupid_name' will appear, you can remove the 'stupid_name' field.

The good thing about this tool is, that it copies the fields. This is nondestructive in most cases. A dangerous situation is to set new_name to an already existing form field.

<div class="bigbox">
<h2>{#field_rename#}</h2>
<p style="margin-top:7px;">
    <form action="{url action=$lastaction}" method="post">
        <label>original_name (Example: 'file_')</label>
        <input type="text" name="original_name" value="{$params.original_name|escape}"/>
        <label>new_name (Example: 'files')</label>
        <input type="text" name="new_name" value="{$params.new_name|escape}"/>
        <hr style="margin:7px;"/>Optional:
        <label>new_type (Example: 'file')</label>
        <input type="text" name="new_type" value="{$params.new_type|escape}"/>
        <label>original_base_name (Example: file)</label>
        <input type="text" name="original_base_name" value="{$params.original_base_name|escape}"/>
        <label>original_supplementary_name (Example: filetext)</label>
        <input type="text" name="original_supplementary_name" value="{$params.original_supplementary_name|escape}"/>
        <label>new_supplementary_type (Example: legend)</label>
        <input type="text" name="new_supplementary_type" value="{$params.new_supplementary_type|escape}"/>
        <hr style="margin:7px;"/>
        <input type="submit" name="preview" value="Preview"/>
        <input type="submit" name="{$renameaction}" value="I know what I'm doing! (a mess)"/>
    </form>
</p>
</div>
{if $field_list}
<div class="bigbox">
<h2>{#field_rename_preview#}</h2>
<table>
    {foreach from=$field_list item=line}
    {if !$header}
    <tr>
        {foreach from=$line key=title item=_}
        <th>{$title}</th>
        {/foreach}
        {assign var=header value=true}
    </tr>
    {/if}
    <tr>
        {foreach from=$line item=item}
        <td>{$item|escape}</td>
        {/foreach}
    </tr>
    {/foreach}
</table>
</div>
{/if}
{include file='footer.tpl'}
