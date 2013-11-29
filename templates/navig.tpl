<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="-1">
    <title>aquarius navigation</title>
    <script src="https://code.jquery.com/jquery.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/navig.css" type="text/css"></link>
    <!--[if IE 6]>
       <link type="text/css" rel="stylesheet" href="css/ie_hacks.css">
    <![endif]-->
    {include_javascript file=javascript.js}
    <base target="mainFrame" />
</head>
<body>

{include file='navig.entry.tpl' menu=$menu->subentries level=0}

<div id="footer">
    aquarius {$smarty.const.AQUARIUS_VERSION} (rev {$revision})<br/>
    &copy; 2001-{$smarty.now|date_format:'%Y'} <a href="http://www.aquaverde.ch/" target="_blank">aquaverde GmbH</a>
</div>

{include file='footer.tpl'}
