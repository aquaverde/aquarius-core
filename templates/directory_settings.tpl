{include file="header.tpl"}
        
    <h1>FileManager : Directory Settings</h1>
	<form method="post" action="{url action=$lastaction}">
		
		<div id="outer">
		<table border="0" cellpadding="3" cellspacing="0">
			<tr>
				<td colspan="2"><strong>Defaults</strong></td>
			</tr>
			<tr>
				<td width="100">Resize Type:</td>
				<td>{$resize}</td>
			</tr>
			<tr>
				<td>Maximal Size:</td>
				<td>{$smarty.const.PICTURE_MAX_SIZE}px</td>
			</tr>
			<tr>
				<td>Thumbnail Size:</td>
				<td>{$smarty.const.PICTURE_TH_SIZE}px</td>
			</tr>
            <tr>
                <td>Alternative Size:</td>
                <td>{$smarty.const.PICTURE_ALT_SIZE}px</td>
            </tr>
		</table>
		</div>
		
		<table border="0" cellpadding="3" cellspacing="0" class="table table-bordered">
			<tr align="left">
				<th>Directory</th>
				<th>Resize Type (max. or width)</th>
				<th>Maximal Size (px)</th>
				<th>Thumbnail Size (px)</th>
				<th>Alternative Size (px)</th>
			</tr>
			
		{foreach from=$dirData item=dir key=name}
			<tr align="left">
				<td>
					{$name}
				</td>
				<td nowrap="nowrap" class="nowrap">
					{html_radios name="dir_setting[$name][resize_type]" options=$typeOptions
       					selected=$dir.resize_type separator=' '}
				</td>
				<td><input type="text" name="dir_setting[{$name}][max_size]" value="{$dir.max_size}" size="5"/></td>
				<td><input type="text" name="dir_setting[{$name}][th_size]" value="{$dir.th_size}" size="5"/></td>
				<td><input type="text" name="dir_setting[{$name}][alt_size]" value="{$dir.alt_size}" size="5"/></td>
			</tr>
		{/foreach}
		</table>
    {action action="dir_settings:save"}
		<input type="submit" name="{$action}" value="Save" class="btn btn-primary" />
        &nbsp;
        {if $show_resize_option}
        <input id="update_thumbnails_check" type="checkbox" name="update_thumbnails" value="1" checked="checked" class="checkbox" />
        <label for="update_thumbnails_check" class="inline">Update thumbnails</label>
        {/if}
    {/action}
	</form>

{include file="footer.tpl"}