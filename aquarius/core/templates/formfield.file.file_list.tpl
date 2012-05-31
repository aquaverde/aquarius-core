<h2>{#s_select_file#}</h2>
<input type="submit" class="close" name="close" value="{#s_close#}" onclick=""/>

{* Give the scrollbox a fixed height in IE7. We tired of trying. Remove this when IE7 is not important anymore *}
{literal}
<!--[if IE 7]>
<style type="text/css" media="screen">
.overlay {height: 380px; top: 10%;}
.overlay .scrollbox {height: 360px}
</style>
<![endif]-->
{/literal}

<div class="scrollbox">
    <table width="100%" cellpadding="3" cellspacing="0" class="table">
        <tr align="left">
            <th>&nbsp;</th>
            <th>{#s_preview#}</th>
            <th>{#s_filename#}</th>
        </tr>
        <tr>
            <td style="text-align:center">
                <input name="select_file" type="radio" id='file_select_none' value=""/>
            </td>
            <td>&mdash;</td>
            <td><label for='file_select_none'>{#s_no_picture#}</label></td>
        </tr>
    {foreach from=$files item=file key=index}
        {if $selected == $file->name()}
            {assign var='is_selected' value=1}
        {else}
            {assign var='is_selected' value=0}
        {/if}
        <tr {if $is_selected}class="selected"{/if}>
            <td width="50" style="text-align:center">
                {if $is_selected}<a name="selected_file"/>{/if}
                <input name="select_file" type="radio" id='file_select_{$index}' value="{$file->name()|escape}" {if $is_selected}checked="checked"{/if}/>
            </td>
            <td width="50" style="text-align:center">
                {include file="formfield.file.thumb.tpl" fileinfo=$file->fileinfo()}
            </td>
            <td>
                <label style='display:inline' for='file_select_{$index}'>{$file->name()|escape}</label>
                ({$file->size('kB')} kB)
            </td>
        </tr>
    {/foreach}
    </table>
</div>