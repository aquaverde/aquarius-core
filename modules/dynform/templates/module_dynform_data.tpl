{include file='header.tpl'}
<h1>Dynform</h1>

<div class="">
	<h2>{#data_manager#}</h2>
	<table border="0" cellpadding="0" cellspacing="0" class="table">
		{dynform_fm_get_forms lg=$lg}
		{foreach from=$dynforms item=dynform}
			<tr class="{cycle values="even,odd"}">
				<td width="1%">
					{action action="dynform_data:show:`$dynform.id`:"}
						<a href="{url action0=$lastaction action1=$action}">
						<img src="buttons/df_db.gif" title="" alt="" />
						</a>
					{/action}
				</td>
					
				<td width="50%">
					{action action="dynform_data:show:`$dynform.id`:"}
						<a href="{url action0=$lastaction action1=$action}">
						{$dynform.title|strip_tags} ({dynform_count_entries form_id=$dynform.id})
						</a>
					{/action}
				</td>
				
				<td width="40%" nowrap="nowrap">
					{foreach_language}
					<div class="dynform-data-lg-link">&nbsp;
						{action action="dynform_data:show:`$dynform.id`:`$entry.lang->lg`"}
							<a href="{url action0=$lastaction action1=$action}">{$entry.lang->lg} ({dynform_count_entries form_id=$dynform.id lg=$entry.lang->lg})</a>
						{/action}
					</div>
					{/foreach_language}
	            </td>
	            <td nowrap="nowrap" align="right">
                    {actionlink action="dynform_data:delete_entries_dialog:`$dynform.id`:" show_title=false}&nbsp;&nbsp;&nbsp;
                    {actionlink action="dynform_data:export:`$dynform.id`:null" show_title=false}&nbsp;
				</td>
			</tr>
		{/foreach}
    </table>
</div>

{include file='footer.tpl'}