{include file='header.tpl'}
<div id="myExit"></div>

{include_javascript file=agenda.js}

<h1>{#agenda#}</h1>
<table cellpadding="0" cellspacing="0" border="0" class="table">
	<tr bgcolor="#e0e7ec">
		<th>&nbsp;</th>
		<th style="width:50px;">{#time#}</th>
		<th>{#title#}</th>
	</tr>
{foreach from=$my_data item=group key=group_name name=group_loop}
	<tr>
		<td style="background:#F2F5F7;width:10px;">
			<div style ="cursor:pointer;" onclick="close_cat_part_poly('{$group_name}',this)" value="closed"><img name="{$group_name}_poly_closer" src="picts/arrow_addon-off.gif" /></div>
		</td>
		<td colspan="9" style="background:#E0E7EC;">
			<div><b>{$group_name}</b></div>
		</td>
	</tr>
	{foreach from=$group item=date key=date_date name=date_loop}
		<tr name="{$group_name}_table_childs" style="display:none">
			<td colspan="2" style="background:#F2F5F7;">
				<div style ="cursor:pointer;" onclick="close_cat_part('{$date_date}','{$group_name}',this)" value="closed"><img name="{$date_date}_{$group_name}_closer" src="picts/arrow_addon-off.gif" /></div>
			</td>
			<td colspan="8" style="background:#E0E7EC;">
				<div><b>{$date_date}</b></div>
			</td>
		</tr>
		{foreach from=$date item=entry key=entry_number name=entry_loop}
			<tr class="{cycle values="odd,even"}" name="{$date_date}_{$group_name}_table_part" overcat="{$group_name}_overcat_child" style="display:none" value="closed">
				<td nowrap="nowrap">
					{action action="contentedit:edit:`$entry->node_id`:`$entry->lg`"}
					<a href="{url action0 = $action action1 = $lastaction}" title="Bearbeiten"><img src="buttons/edit.gif" title="Bearbeiten" alt="Bearbeiten"/></a>
					{/action}
		       	</td>
				<td nowrap="nowrap">{$entry->time}</td>
				<td nowrap="nowrap">
					{action action="contentedit:edit:`$entry->node_id`:`$entry->lg`"}
					<strong>
						<a href="{url action0 = $action action1 = $lastaction}" title="Bearbeiten">{$entry->title}</a>
					</strong>
					{/action}					
				</td>
			</tr>
		{/foreach}
	{/foreach}
{/foreach}	
</table>