{include file="header.tpl"}

<script language="JavaScript" type="text/javascript">
<!--
	var progressBar	= '';
	var fileArray	= '{$existingFiles}'.split(',');

	function checkForFile(file) {ldelim}
		var chunks	= file.value.split('/');

		var fileName = chunks[chunks.length-1];

		for ( var i = 0 ; i < fileArray.length ; i++ )
			if ( fileArray[i] == fileName ) {ldelim}
				alert(fileName + ': {#s_overwrite_warn#}');
			{rdelim}
	{rdelim}

//-->
</script>


	<h1>FileManager : Upload</h1>

<div class="topbar">   
    <h3>{#s_directory#}</h3>
    
    <form name="fileMGR" action="{url action0=$lastaction}" method="post" enctype="multipart/form-data">
	<input type="hidden" name="doClosePopUp" value="3" />
	<select name=selectedDir onChange="document.fileMGR.submit()">
		{html_options values=$availableDirectories output=$availableDirectories selected=$selectedDir}
	</select>
	
	&nbsp;&nbsp; {#s_filesquantity#}: <select name="fileCount" onChange="document.fileMGR.submit()">
	
		{section name=loop start=1 loop=$maxFileUpload}
			<option value="{$smarty.section.loop.index}" {if $fileCount == $smarty.section.loop.index}selected="selected"{/if}>{$smarty.section.loop.index} {#s_files#}</option>
		{/section}		
	</select>
	
</div>
    <table cellpadding="3" cellspacing="0" class="table table-bordered">
	    <tr align="left">
	    	<th>{#s_select_file#}</th>
	    </tr>
	    
	{section name=loop start=0 loop=$fileCount}
	
		<tr>
			<td>
				<input type="file" size="50" maxlength="0" name="file{$smarty.section.loop.index}" onchange="checkForFile(this)" />
				<input type="checkbox" name="file{$smarty.section.loop.index}_zip" value="1"> {#is_zip_file#}
			</td>
		</tr>
	{/section}

	</table>
	<p>
		<input type="submit" name="{$upload_files_action}" value="Upload Files" class="btn btn-primary" />
    </p>
   	</form>

{include file="footer.tpl"}