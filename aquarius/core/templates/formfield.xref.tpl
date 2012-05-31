<table border="0" cellspacing="0" cellpading="0">
{foreach from=$field.xref item=item key=index}
	<tr style="height:30px;">
		<td style="width:20px">&nbsp;</td>
		<td>{$item.title}</td>
		<td style="width:20px">&nbsp;</td>
		<td>
			<input type="hidden" name="{$field.formname}[{$index}][cat]" value="{$item.node_id}" />
			{assign var=selected_id value=$field.value[$item.node_id]}
			{html_options name="`$field.formname`[$index][node]" options=$item.entries selected=$selected_id}
		</td>
	</tr>
{foreachelse}
	<tr><td><i>{#no_categories_assigned#}</i></td></tr>
{/foreach}
</table>