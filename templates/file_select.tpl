{include file="header.tpl" bodyclass=file_select htmltitle=$lastaction->get_title()}
{include_javascript file='prototype.js' lib=true}
{js target_id=$target_id subdir=$subdir callback=$callback}{literal}
    function select_file(file) {
        opener.$callback('$target_id', file)
        window.close()
        return false
    }
{/literal}{/js}
{if $file_uploaded}
    {js}
        select_file('{$file_uploaded|escape}');
    {/js}
{/if}
{js}
    window.scrollbars.visible = true;
{/js}
<h1>{$lastaction->get_title()}</h1>
<div class="topbar">
    <div id="div_upload_box" style="margin:5px 0 5px 0;">
        <form action="{url action=$lastaction}" enctype="multipart/form-data" method="post" accept-charset="utf-8">
            <h2>{#s_upload_picture#}</h2>
            <input type="file" id="input_file_upload" name="input_file_upload" />
            <button type="submit" name="btn_upload_file" value="{#upload_starten#}" id="btn_upload_file" class="btn btn-sm btn-primary" style="margin-top :8px" /><span class="glyphicon glyphicon-upload"></span> {#upload_starten#}</button>
        </form>
	</div>
    <div class="clear"></div>
</div>
<h2>{#s_choose#}</h2>
<div style="padding-bottom: 15px;">
    <div style="float:right">
        {foreach from=$view_actions item=view_action}
            {actionlink action=$view_action return=0 show_title=0}
        {/foreach}
    </div>
    {if !$filter}
        <button id="show_filter" class="btn btn-default btn-sm" name="show_filter" value="{#s_search#}..." onclick="$('show_filter').hide(); $('filter_box').show(); return false"><span class="glyphicon glyphicon-search"></span> {#s_search#}</button>
    {/if}
    <form action="{url action=$change_action}" method="post">
        <div>            
        {if $subdirs}
            <div>
                <label for="subdir_select" style="display: inline">{#s_directory#}</label>
                <select name="subdir" id="subdir_select" onchange="form.filter.value='';form.submit()">
                    <option value="">{#s_choose_dir#}</option>
                    <optgroup label="----">
                {foreach from=$subdirs item=dir}
                        <option value="{$dir}"{if $subdir == $dir} selected="selected"{/if}>{$dir}</option>
                {/foreach}
                    </optgroup>
                </select>
            </div>
        {/if}
        </div>
        <div style="{if $subdirs} margin-top: 10px;{/if}{if !$filter}display: none{/if}" id="filter_box">
            <input placeholder="{#s_search#}" type="text" id='filterinput' name="filter" value="{$filter}" class="form-control" style="display:inline; width:30%;"/>
            &nbsp;<input type="submit" name="doFilter" value="{#s_filter_it#}" class="btn btn-sm btn-default" />
            &nbsp;<input type="submit" name="reset" value="{#s_filter_reset#}" onclick="form.filter.value='';form.submit()" class="btn btn-sm btn-default" />
        </div>
    </form>
    <div class="clear"> </div>
</div>
{include file=page_select.tpl lastaction=false}
{if $browse}
<div id="browse">
    <div style="padding: 5px; padding-left: 8px">
        <input name="select_file" type="radio" id='file_select_empty' value=""  onclick="select_file('')" />
        <label for='file_select_empty' style='display: inline' title="{#s_no_picture#}">
            {#s_no_picture#}
        </label>
    </div>
    <ul>
    {if $files|@count == 0}
        <li style="width: 100%;"><b>{#s_no_files_found#}</b></li>
    {/if}
    {if $files}
        {foreach from=$files item=file key=index}
        <li class="{if $file->selected} selected{/if}" onclick="return select_file('{if $subdir}{$subdir}/{/if}{$file->name()|escape}')">
            {thumbnail file=$file class="superthumb" show_filename=true}
        </li>
        {/foreach}
    {/if}
    </ul>
    <div class="clear"></div>
</div>
{else}
<table id='filelist' width="100%" class="table table-bordered">
    <tr align="left">
        <th width="25">&nbsp;</th>
        <th>{#s_preview#}</th>
        <th>{#s_filename#}</th>
    </tr>
    <tr {if $file->selected}class="selected"{/if}>
        <td style="text-align:center">
            <input name="select_file" type="radio" id='file_select_none' value="" onclick="select_file('')"/>
        </td>
        <td colspan="2">
            <label for='file_select_none'>{#s_no_picture#}</label>
        </td>
    </tr>
    {if $files}
        {foreach from=$files key=index item=file}
    <tr class="{if $file->selected}selected{/if}" onclick="return select_file('{if $subdir}{$subdir}/{/if}{$file->name()|escape}')">
        <td style="text-align:center">
            <input name="select_file" type="radio" id='file_select_{$index}' value="" {if $file->selected}checked="checked"{/if}/>
        </td>
        <td width="50" style="text-align:center">
            {thumbnail file=$file}
        </td>
        <td>
            <label for='file_select_{$index}'>{$file->name()|escape}</label>
        </td>
    </tr>
        {/foreach}
    {/if}
</table>
{/if}
<input type="submit" name="" value="{#s_close#}" onclick="window.close()" class="btn btn-sm btn-default" style="margin: 10px 0 15px 0; float:right"/>
{include file=page_select.tpl lastaction=false}
{include file="footer.tpl"}
