<table width="100%" cellpadding="3" cellspacing="0" class="table">
    	<tr align="left">
    		<th><input type="checkbox" onchange="selectAll(document.select_files, this)"></th>
    		<th>{#s_preview#}</th>
    		<th>&nbsp;</th>
    		<th>{#s_filename#}</th>
    		<th>{#s_dimensions#}</th>
    		<th>{#s_filesize#}</th>
    		<th>{#s_references#}</th>
    	</tr>
	{foreach from=$files item=fileinfo name=fList key=index}
        {assign var="file" value=$fileinfo.file} 
        {if $hasSpinner}
            {assign var="show" value=$spinner->show($index)}
        {/if}
		<tr>
    		<td width="15">
            {if $fileinfo.may_delete }
                <input type="checkbox" name="fileChk[]" value="{$file->name()}"/>&nbsp;
            {else}
                &nbsp;
            {/if}
			</td>
    		<td width="50">
                {include file="filemgr_thumbnail.tpl"}
			</td>
    		<td width="15">
                <a href="{$fileinfo.publicpath|download}"><img src="picts/download.gif" alt="Download" title="Download"/></a>
			</td>
    		<td><a href="{$fileinfo.publicpath}" target="_blank">{$file->name()}</a></td>
		 	<td>{if $fileinfo.type == "image"}{$fileinfo.size.0}x{$fileinfo.size.1}px{else}&nbsp;{/if}</td>
    		<td>{$file->size('kB')}&nbsp;kB</td>
    		<td>
    		<div class="ref_cell">
            {foreach from=$fileinfo.references item=content name=refs}
                {assign var="editaction" value="contentedit:edit:`$content->node_id`:`$content->lg`"|makeaction}
                {if $editaction}<a href="{url action0=$editaction action1=$lastaction}" class="little">>{/if}
                {$content->cache_title|strip_tags|truncate:50}{if $lg != $content->lg} ({$content->lg}){/if}
                {if $editaction}</a>{/if}
                {if !$smarty.foreach.refs.last}<br/>{/if}
            {/foreach}
            &nbsp;
            </div>
    		</td>
    	</tr>
	{/foreach}
</table>