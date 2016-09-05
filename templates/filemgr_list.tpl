<table width="100%" class="table table-bordered">
    	<tr align="left">
    		<th>{#s_preview#}</th>
    		<th>&nbsp;</th>
    		<th>{#s_filename#}</th>
    		<th>{#s_dimensions#}</th>
    		<th>{#s_filesize#}</th>
    	</tr>
	{foreach from=$files item=fileinfo name=fList key=index}
        {assign var="file" value=$fileinfo.file} 
        {if $hasSpinner}
            {assign var="show" value=$spinner->show($index)}
        {/if}
		<tr>
    		<td width="50">
                {include file="filemgr_thumbnail.tpl"}
			</td>
    		<td width="15">
                <a href="{$fileinfo.publicpath|download}" title="Download" data-toggle="tooltip"><span class="glyphicon glyphicon-download"></span></a>
			</td>
    		<td><a href="{url action=$lastaction action2=$fileinfo.detail}" title="{#s_open#}" data-toggle="tooltip">{$file->name()}</a></td>
		 	<td>{if $fileinfo.type == "image"}{$fileinfo.size.0}x{$fileinfo.size.1}px{else}&nbsp;{/if}</td>
    		<td>{$file->size('kB')}&nbsp;kB</td>
    		<td>
    		</td>
    	</tr>
	{/foreach}
</table>