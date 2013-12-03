<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
		<title>{$htmltitle|default:"aquarius cms"}</title>
        <script src="https://code.jquery.com/jquery.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>          
        
        {include_javascript file=javascript.js}
        {if $page_requisites|default:false}
            {foreach from=$page_requisites->load_js_lib() item=script}
                    <script type="text/javascript" charset="utf-8" src="{$script}"></script>
            {/foreach}
            {foreach from=$page_requisites->managed_js item=mjs}
                {include_javascript file=$mjs.file lib=$mjs.lib}
            {/foreach}
            {foreach from=$page_requisites->managed_css item=mcss}
                {include_css file=$mcss.file}
            {/foreach}
        {/if}
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/admin.css" type="text/css" />
        <!--[if lt IE 9]>
        <link rel="stylesheet" href="css/ie.css" type="text/css" />
        <![endif]-->
		<link rel="stylesheet" href="css/dynform.css" type="text/css" />
		<link rel="shortcut icon" type="image/png" href="./favicon.png" />

	</head>
	<body class="{$bodyclass|default:'admin'}">
    <div class="wrapper">
	{include file='messages.tpl'}
    {if $navig_reload_node}
        {js navig_reload_node=$navig_reload_node->id}{literal}
            if (parent && parent.leftFrame && parent.leftFrame.nodetree)
            parent.leftFrame.nodetree.update($navig_reload_node, {})
        {/literal}{/js}
    {/if}