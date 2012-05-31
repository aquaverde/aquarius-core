<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
    <title>Pointing Selector</title>
    <meta http-equiv="content-type" content="text/html; charset=windows-1252" />
    {include_javascript file=javascript.js}
    <link rel="stylesheet" href="css/admin.css" type="text/css" />
</head>
<script type="text/javascript">
<!--
{literal}

function savePointings() {
    // Get array of checkboxes that are checked
    var elems = collectionToArray(document.pointingForm.elements);
    var checked = elems.filter(function(elem) { return elem.type == "checkbox" && elem.checked });

    // comma separated list of values from the values of the checked elements
    var result = checked.map(function(elem) {return elem.value} ).join(",");
{/literal}
    // Write it back to main window
    opener.document.contentedit['fields[{$target}]'].value = result;

    var targetDiv       = opener.document.getElementById('pointingDiv{$target}');
    targetDiv.innerHTML = '( '+ checked.length + ' {#s_pointings_selected#} )';
{literal}
    window.close();
}

function init() {
{/literal}
    var pointings = opener.document.contentedit['fields[{$target}]'].value.split(",");
{literal}
    var elems = collectionToArray(document.pointingForm.elements);
    elems.forEach( function(elem) {
        if (pointings.indexOf(elem.value) >= 0) elem.checked = true;
    });
}

{/literal}
//-->
</script>
<body bgcolor="#FFFFFF" onload="init()" style="margin:10px;">

<form action="" name="pointingForm" >
{strip}
<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table">
	<tr align="left">
		<th>Pointing Selector</th>
	</tr>
	<tr>
		<td style="padding: 15px;">
			<table border="0" cellpadding="0" cellspacing="0" class="table2" style="margin-top:0px;">
				
{foreach from=$nodelist item=nodeinfo}

			<tr class="{cycle values="even,odd"}">
				<td nowrap="nowrap">
				{foreach from=$nodeinfo.connections item=connection}
					<img src="picts/{$connection}.gif" alt="" style="vertical-align:middle" />
				{/foreach}
			
				{*if $rootDisplayed *}
					<input type="checkbox" name="point{$nodeinfo.node->id}"" value="{$nodeinfo.node->id}" class="checkbox" />
				{*/if*} 
					{$nodeinfo.node->get_contenttitle()}
				</td>
				<td>&nbsp;</td>
			</tr>
	{*assign var="rootDisplayed" value=true*}
{/foreach}
			</table>
		</td>
	</tr>
</table>
	<input type="button" name="" value=" OK " class="submit" onclick="savePointings();" />
	<input type="button" name="" value="Cancel" class="cancel" onclick="window.close();" />
{/strip}
</form>
</body>
</html>