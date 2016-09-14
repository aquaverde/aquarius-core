<table width="100%" class="table table-bordered">
    	<tr align="left">
    		<th>{#s_preview#}</th>
    		<th>{#s_filename#}</th>
            <th>{#s_last_change#}</th>
    	</tr>
	{foreach from=$files item=fileinfo name=fList key=index}
        {assign var="file" value=$fileinfo.file} 
        {if $hasSpinner}
            {assign var="show" value=$spinner->show($index)}
        {/if}
		<tr>
    		<td width="50">
                <a href="{url action=$lastaction action2=$fileinfo.detail}" alt="{$attrs.name}" title="{#s_open#}" data-toggle="tooltip">
            {if $fileinfo.type == "image"}
                    <img src="{$fileinfo.publicpath|th}" alt="{#s_show_file#}" title="{#s_show_file#}" data-toggle="tooltip"/>
            {else}
                    <img src="buttons/{$fileinfo.button}" alt="{$fileinfo.name}" title="{$fileinfo.name}"/></a>
            {/if}
                </a>
			</td>
    		<td><a href="{url action=$lastaction action2=$fileinfo.detail}" title="{#s_open#}" data-toggle="tooltip">{$file->name()}</a></td>
            <td>{$file->mtime()|date_format}</td>
    	</tr>
	{/foreach}
</table>