<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{$htmltitle|default:"aquarius cms"}</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<!--[if lt IE 9]>
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
        <![endif]-->
         
        {include_javascript file='javascript.js'}
        
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
        
        <link rel="stylesheet" href="css/admin.css" type="text/css" />
        <!--[if lt IE 9]>
        <link rel="stylesheet" href="css/ie.css" type="text/css" />
        <![endif]-->
		<link rel="stylesheet" href="css/dynform.css" type="text/css" />
		<link rel="shortcut icon" type="image/png" href="./favicon.png" />

	</head>
	<body class="{$bodyclass|default:'admin'}">
	{include file='messages.tpl'}
    {if $navig_reload_node}
        {js navig_reload_node=$navig_reload_node->id}{literal}
            if (parent && parent.leftFrame && parent.leftFrame.nodetree)
            parent.leftFrame.nodetree.update($navig_reload_node, {})
        {/literal}{/js}
    {/if}