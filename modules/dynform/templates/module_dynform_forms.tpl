{include file='header.tpl'}
<h1>{#forms_overview#}</h1>

<div class="">
	<h2>{#forms_manager#}</h2>
	<table border="0" cellpadding="0" cellspacing="0" class="table">
		{dynform_fm_get_forms lg=$lg}
		{foreach from=$dynforms item=dynform}
			<tr class="{cycle values="even,odd"}">
				<td>
				<div class="fm_form_icon">
					{action action="contentedit:edit:`$dynform.node_id`:`$lg`"}
						<a href="{url action0=$lastaction action1=$action}">
						<img src="buttons/df_form.gif" title="" alt="" />
						</a>
					{/action}
				</div>
					
				<div class="fm_title">
					{action action="contentedit:edit:`$dynform.node_id`:`$lg`"}
						<a href="{url action0=$lastaction action1=$action}">
						{$dynform.title}
						</a>
					{/action}
				</div>
				<div class="fm_command">
					<div class="dynform-control-icon">&nbsp;
						{action action="contentedit:edit:`$dynform.node_id`:`$lg`"}
							<a href="{url action0=$lastaction action1=$action}"><img src="buttons/df_edit.gif" alt="" title="" /></a>
						{/action}
					</div>
					
					<div class="dynform-control-icon">&nbsp;
						{confirm yes="contentedit:delete:`$dynform.node_id`:`$lg`"
							 no=''
							 title=$smarty.config.s_delete_content
							 message=$smarty.config.s_confirm_delete_content|sprintf:$dynform.title}
							
							<a href="{url action1=$action}" title="{#s_delete_content#} [{$dynform.title}]"><img src="buttons/delete.gif" alt="{#s_delete_content#}" class="delete" /></a>
						{/confirm}
						{*/*
						{confirm yes="contentedit:delete:`$node->id`:`$content->lg`"
						{action action="contentedit:delete:`$dynform.node_id`:`$lg`"}
							<a href="{url action0=$lastaction action1=$action}"><img src="buttons/df_trash.gif" alt="" title="" /></a>
						{/action}
						*/*}
					</div>
				</div>
				</td>
			</tr>
		{/foreach}
    </table>
</div>

{include file='footer.tpl'}