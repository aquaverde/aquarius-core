{include file='header.tpl'}

<div id="myExit"></div>

<h1>{#map#}</h1>
<div id="map"></div>
	<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key={php}echo MAP_KEY;{/php}" type="text/javascript"></script>
	{include_javascript file='googlemaps.js'}
	<script type="text/javascript">
		init_map('{$myXml}');
	</script>

<br/>

<h1>{#points#}</h1>
<table cellpadding="0" cellspacing="0" border="0" class="table">
	<tr bgcolor="#e0e7ec">
		<th>&nbsp;</th>
		<th>{#title#}</th>
		<th>{#description#}</th>
		<th>Link</th>
		<th>Marker</th>
	</tr>
{foreach from=$content_list_points item=content_list key=name}
	<tr>
		<td style="background:#F2F5F7;width:10px;">
			<div style ="cursor:pointer;" onclick="close_cat_part('{$name}',this)" value="closed"><img name="{$name}_closer" src="picts/arrow_addon-off.gif" /></div>
		</td>
		<td colspan="9" style="background:#E0E7EC;">
			<div style ="cursor:pointer;" onclick="show_cat_only('{$name}','cat')"><b>{if $name=="-"}{#no_categorised#}{else}{$name}{/if}</b></div>
		</td>
	</tr>	
	{foreach from=$content_list item=marker}
		<tr class="{cycle values="odd,even"}" name="{$name}_table_part" style="display:none;" value="closed">
			<td nowrap="nowrap">
				{action action="contentedit:edit:`$marker.node_id`:`$marker.lg`"}
				<a href="{url action0 = $action action1 = $lastaction}" title="{#edit#}"><img src="buttons/edit.gif" title="{#edit#}" alt="{#edit#}"/></a>
				{/action}
	       	</td>
			<td nowrap="nowrap">
			    {action action="contentedit:edit:`$marker.node_id`:`$marker.lg`"}
			        <a href="{url action0 = $action action1 = $lastaction}" title="{#edit#}">{$marker.title}</a>
			    {/action}
			</td>
			<td nowrap="nowrap">{$marker.desc}</td>
			
			<td nowrap="nowrap"><a href="{$marker.link}">{$marker.link}</a></td>
			{if $marker.icontype eq "-"}
				<td nowrap="nowrap">-</td>
				{else}
					<td nowrap="nowrap"><img src="/pictures/gmap_icons/{$marker.icontype}" /></td>
			{/if}
		</tr>
	{/foreach}
{/foreach}	
</table>

<br />
<h1>{#polylines#}</h1>
<table cellpadding="0" cellspacing="0" border="0" class="table">
	<tr bgcolor="#e0e7ec">
		<th>&nbsp;</th>
		<th>{#title#}</th>
		<th>{#description#}</th>
		<th>Link</th>
		<th>Marker</th>
	</tr>
{foreach from=$content_list_lines item=content_list key=name}
	<tr>
		<td style="background:#F2F5F7;width:10px;">
			<div style ="cursor:pointer;" onclick="close_cat_part_poly('{$name}',this)" value="closed"><img name="{$name}_poly_closer" src="picts/arrow_addon-off.gif" /></div>
		</td>
		<td colspan="9" style="background:#E0E7EC;">
			<div style ="cursor:pointer;" onclick="show_cat_only('{$name}','overcat')"><b>{if $name=="--"}{#no_categorised#}{else}{$name}{/if}</b></div>
		</td>
	</tr>
	{foreach from=$content_list item=under key=name2}
		<tr name="{$name}_table_childs" style="display:none">
			<td style="background:#F2F5F7;">
				<div style ="cursor:pointer;" onclick="close_cat_part('{$name2}',this)" value="closed"><img name="{$name2}_closer" src="picts/arrow_addon-off.gif" /></div>
			</td>
			<td colspan="9" style="background:#E0E7EC;">
				<div style ="cursor:pointer;" onclick="show_cat_only('{$name2}','cat')"><b>{$name2}</b></div>
			</td>
		</tr>
		{foreach from=$under item=marker}
			<tr class="{cycle values="odd,even"}" name="{$name2}_table_part" overcat="{$name}_overcat_child" style="display:none" value="closed">
				<td nowrap="nowrap">
					{action action="contentedit:edit:`$marker.node_id`:`$marker.lg`"}
					<a href="{url action0 = $action action1 = $lastaction}" title="{#edit#}"><img src="buttons/edit.gif" title="{#edit#}" alt="{#edit#}"/></a>
					{/action}
		       	</td>
				<td nowrap="nowrap">{$marker.title}</td>
				<td nowrap="nowrap">{$marker.desc}</td>
				<td nowrap="nowrap"><a href="{$marker.link}">{$marker.link}</a></td>
				{if $marker.icontype eq "-"}
					<td nowrap="nowrap">-</td>
					{else}
						<td nowrap="nowrap"><img src="/pictures/gmap_icons/{$marker.icontype}" /></td>
				{/if}
			</tr>
		{/foreach}
	{/foreach}
{/foreach}	
</table>

{include file='footer.tpl'}