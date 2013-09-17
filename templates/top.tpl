<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>aquarius topframe</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="css/top.css" type="text/css"></link>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
	<script language="JavaScript" type="text/javascript">
	<!--
	{literal}
	var actualSelected;
	//v2.0
	function openBrWindow(theURL,winName,features) { 
		mypop = window.open(theURL,winName,features);
		mypop.focus();  //fixed focus bug ..gruss gc
	}
    
    $(document).ready (function() {
        // input text label
        $(":input[title]").each(function() {
            var $this = $(this);
            if($this.val() === '') {
                $this.val($this.attr('title'));
            }
            $this.focus(function() {
                if($this.val() === $this.attr('title')) {
                    $this.val('');
                }
            });
            $this.blur(function() {
                if($this.val() === '') {
                    $this.val($this.attr('title'));
                }
            });
        });
    });
	{/literal}
	-->
	</script>
</head>
<body>
    <div id="header">
        <div id="logo">
            <a href="./" target="_parent"><img src="picts/logo.gif" border="0" style="vertical-align: middle;" alt="aquarius version {$smarty.const.AQUARIUS_VERSION}" title="aquarius version {$smarty.const.AQUARIUS_VERSION}" /></a>
        </div>
        <div class="title">
       {if $user->isSuperadmin()}
            <form action="{url url=$url action="nodetree:show:super:"}" method="post" target="mainFrame">{* Insane... in the mainFrame.            Sorry could not resist. *}
                {actionlink action="cache_cleaner:all"}
                {actionlink action="echo_cookie:set"}
                <input type="hidden" name="loglevel" value="20"/>
                <input type="hidden" name="firelevel" value="1000"/>
            </form>
        {/if}
            <a href="{$smarty.const.PROJECT_URL}{$lg}/" target="_blank" title="Open Website">{$smarty.const.PROJECT_TITLE}</a>
        </div>
    </div>		
    <div id="navig">
        <ul id="mainNavig">
            {foreach from=$menu->subentries item='entry'}
                {assign var='name' value=$entry->name}
                <li{if $smarty.get.display == $entry->name} class="on"{/if}>
                    <a href="{$frameset_url->with_param('display', $name)}" id="{$name}" target="_parent">
                        {$smarty.config.$name}
                    </a>
                </li>
            {/foreach}
        </ul>
        <div id="serviceNavig">            
            <div id="lang">
                {* Show language selection dropdown only if there's more than one language to choose from *}
                {if $languages|@count > 1}
                    <form name="lang" action="./" target="_top" method="post">
                    <span class="label">{#s_language#}</span>&nbsp;
                        <select name="lg" onchange="document.lang.submit()">
                            {html_options options=$languages selected=$lg}
                        </select>
                    </form>
                {/if}
            </div>
            &nbsp;|&nbsp;&nbsp;<a href="http://wiki.aquarius3.ch/" target="_blank">{#s_help_title#}</a>&nbsp;&nbsp;|&nbsp;&nbsp;
            <form method="post" action="{url action="search:"}" target="mainFrame">
                <input type="text" name="search" value="{$lastSearch}" title="{#s_suche#}" style="height: 12px;vertical-align: middle;" />&nbsp;&nbsp;|&nbsp;
            </form>
            hello, {$user->name}&nbsp;&nbsp;|
            <form action="{url action0="logout:now"}" method="post" target="mainFrame">
                <input class="logout" type="submit" value="Logout" />
            </form>        
        </div>
        <div id="subNavig"></div>
    </div>
</body>
</html>
