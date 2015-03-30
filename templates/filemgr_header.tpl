<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
	<head>
		<title>aquarius FileManager</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta http-equiv="cache-control" content="no-cache" />
        <script src="https://code.jquery.com/jquery.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>     
        {include_javascript file='javascript.js'}
    {if !$isPopup }
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link rel="stylesheet" href="css/admin.css" type="text/css" />
    {else}
        <link rel="stylesheet" href="css/popup.css" type="text/css" />
    {/if}
		
	</head>

<body {if $isPopup}onload="getSelectedFromOpener();"{/if} bgcolor="#FFFFFF">
<div class="wrapper">
{include file='messages.tpl'}
<script language="JavaScript" type="text/javascript">
<!--
{if $isPopup}
    function doSelection(fileName, button, filetype) {ldelim}
    
        var selector    = opener.document.getElementById("{$fieldID}");
        
        if ( fileName == '{#s_no_picture#}' ) {ldelim}
            selector[1].selected = true;
            selector.selectedIndex = 1;
            opener.document.getElementById("{$fieldID}_img").src = "picts/spacer.gif";
        {rdelim}
        else
            for ( var i = 0 ; i < selector.length ; i++ ) {ldelim}
                if ( selector[i].value == fileName ) {ldelim}
                    selector[i].selected = true;
                    if ( filetype == 'image' )
                        opener.document.getElementById("{$fieldID}_img").src = "{$project_url}/{$selectedDir}/th_" + fileName;
                    else
                        opener.document.getElementById("{$fieldID}_img").src = "buttons/" + button;
                {rdelim}
            {rdelim}
        
    {rdelim}
    
    function getSelectedFromOpener() {ldelim}
        var selectedValue   = opener.document.getElementById("{$fieldID}").value
        {literal}
        var radios = document.getElementsByTagName("input")
        radios[0].checked = true
        var first = false
        collectionToArray(radios).forEach(function(radio) {
            if (radio.type == 'radio') {
                if (radio.value == selectedValue ) {
                    radio.checked = true
                }
            }
        });
        {/literal}
        
    {rdelim}
{/if}
//-->
</script>

{if !$isPopup}
    <h1>FileManager</h1>
    <div class="topbar">
{else}
    <h3>{#s_select_file#}</h3>
{/if}


    <div style="float:right; {if $isPopup}margin-top: -20px;{/if}">
        <a href="{url action=$browseaction}">
            <span class="glyphicon glyphicon-th{if $managerStyle == "browse" } on{/if}"></span>

        </a>&nbsp;
        <a href="{url action=$listaction}">
            <span class="glyphicon glyphicon-th-list {if $managerStyle == "list"}on{/if}"></span>
        </a>
    </div>

{if !$isPopup}
    <h3>{#s_directory#}<span class="light"> ({#s_view_th#}: {$dir_props->th_size}px {if $dir_props->alt_size > 0}| Alt: {$dir_props->alt_size}px {/if}| Max: {$dir_props->max_size}px)</span></h3>

    <form name="fileMGR" method="post" action="" class="form-inline">
		<select name="selectedDir" onchange="document.fileMGR.submit()">
			{html_options values=$availableDirectories output=$availableDirectories selected=$selectedDir}
    	</select>

		&nbsp;{#s_filter#}: <input type="text" name="fileFilter" value="{$fileFilter}" class="form-control" />
		&nbsp;<input type="submit" name="doFilter" value="{#s_filter_it#}" class="btn btn-xs" />
		&nbsp;<input type="button" name="reset" value="{#s_filter_reset#}" 
			onclick="document.fileMGR.fileFilter.value = ''; document.fileMGR.submit();" class="btn btn-xs" />
	</form>
    </div>
{/if}

          