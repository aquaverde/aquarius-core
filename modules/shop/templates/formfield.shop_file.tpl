
<table cellpadding="0" cellspacing="0" border="0" class="table darker">
    <tr>
        <td width="40" align="center" style="vertical-align:middle" rowspan="2" nowrap="nowrap" class="hell">
{if $value}
            <a href="/{$smarty.const.SHOP_PICTURE_FOLDER}{$value}" target="_blank"><img src="/{$smarty.const.SHOP_PICTURE_FOLDER}{$value|th}" id="{$id}_img" vspace="3" hspace="5" border="0" alt="{$value}"/></a>
{else}
            <img src="picts/spacer.gif" id="{$id}_img" vspace="3" hspace="5" border="0" alt="{$value}"/>
{/if}
        </td>
        <td valign="top" height="12" nowrap="nowrap">
{assign var="selectedDir" value=$smarty.const.SHOP_PICTURE_FOLDER}
{assign var="fieldID" value=$field.htmlid}
          
{if $smarty.const.DEFAULT_MANAGER_STYLE == "browse"}
    {assign var="popupStyle" value="browse"}
    {assign var="mgrIcon" value="browser"}
{else}
    {assign var="popupStyle" value="list"}
    {assign var="mgrIcon" value="browser_list"}
{/if}
            <div style="float:right">{#s_upload_picture#}: <input type="file" size="10" name="{$name}_newfile"/></div>
            <select onchange="changeSelectedPicture('{$id}_img', this, '/{$smarty.const.SHOP_PICTURE_FOLDER}')" name="{$name}[picture]" id="{$id}">
                <option value="">{#s_choose#}</option>
                <optgroup label="----">
                    <option value="">{#s_no_picture#}</option>
                </optgroup>
                <optgroup label="----">
                
{foreach from=$smarty.const.SHOP_PICTURE_FOLDER|listFiles item=file}
                    <option value="{$file}"{if $value == $file} selected="selected"{/if}>{$file}</option>
{/foreach}

                </optgroup>
            </select>
            {action action="filemgr:$popupStyle:`$smarty.const.SHOP_PICTURE_FOLDER`::`$value`:$id:popup"}
                <a  href="javascript:openBrWindow('admin.php?lg=$lg&amp;{$action}','FileManager','height=350,width=450,top=100,left=200,toolbar=no,status=yes,resizable=yes,menubar=no,scrollbars=yes');"><img src="buttons/{$mgrIcon}.gif" valign="absmiddle" alt="" title="" style="vertical-align: text-bottom;"/></a>
            {/action}
            
        </td>
    </tr>
    <tr>
    </tr>
</table>