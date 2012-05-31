<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head>
    <title>{transform function='strip_tags'}{strip}
    {block name='title'}
        {load node=root var=root}
        {if $content->htmltitle}{$content->htmltitle}{else}{$root->htmltitle} | {nodepath_html_title exclude="1,"}{/if}
    {/block}
    {/strip}{/transform}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="{$lg}" />
    <meta name="description" content="{getfield node=$node field='metadescription' inherit=true}" />
    <meta name="keywords" content="{getfield node=$node field='metakeywords' inherit=true}" />
    <meta name="robots" content="index,follow" />
    <meta name="revisit-after" content="3 days" />
    <meta name="robots" content="all" />
    <meta name="author" content="aquaverde.ch" />
    <meta name="generator" content="CMS aquarius {$smarty.const.AQUARIUS_VERSION}" />
    <link type="image/x-icon" rel="shortcut icon" href="/favicon.ico" />
    <link type="image/png" rel="icon" href="/favicon.png" />    
    <link href="/css/global.css" rel="stylesheet" type="text/css" media="all" />
    <link href="/css/form.css" rel="stylesheet" type="text/css" media="all" />
    <link href="/css/print.css" rel="stylesheet" type="text/css" media="print" />
    <script src="/lib/inhouse/javascript.js" type="text/javascript"></script>
    <script src="/lib/jquery/jquery-1.6.2.min.js" type="text/javascript"></script>
    <script src="/lib/jquery/jquery.hoverIntent.min.js" type="text/javascript"></script>
    <script src="/lib/jquery/jquery.coda-slider.min.js" type="text/javascript"></script>
    <script src="/lib/jquery/jquery.easing.min.js" type="text/javascript"></script>
    <script src="/lib/jquery/jquery.browser.min.js" type="text/javascript"></script>
    <script src="/lib/jquery/jquery.init.js" type="text/javascript"></script>
{if $node->name==contact}
    <script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
{/if}
</head>
<body>
{block name=body}{/block}
{$root->googleanalytics}
</body>
</html>