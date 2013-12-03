<table width="100%" class="table table-bordered">
		<tr class="nohover">
			<td>
				<div id="browse" style="border:none;">
					<ul>
			{if $files|@count == 0}
				<li style="width: 100%;"><b>{#s_no_files_found#}</b></li>
			{/if}
			
			{foreach from=$files item=fileinfo name=fList key=index}
                {assign var="file" value=$fileinfo.file}
				{if $hasSpinner}
					{assign var="show" value=$spinner->show($index)}
				{/if}
                <li>
                    <div class="pict">
                {include file="filemgr_thumbnail.tpl"}
                <p>
                    {if $fileinfo.may_delete }
                        <input type="checkbox" name="fileChk[]" value="{$fileinfo.name}"/>&nbsp;
                    {/if}
                    <a title="{$file->name()}" style="cursor:help;">{$file->name()|truncate:15:"...":false:true}</a>
                </p>
                    </div>
                </li>
			{/foreach}
					</ul>
				</div>	
    			</td>
			</tr>
		</table>