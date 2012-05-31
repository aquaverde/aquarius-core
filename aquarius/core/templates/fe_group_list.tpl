{include file='header.tpl'}

<h1>{#s_groups#}</h1>

<div class="bigbox">
	<div class="bigboxtitle"><h2>{#s_groups#}</h2></div>

	<table width="100%" border="0" cellpadding="0" cellspacing="0"  class="table2" style="margin-top:10px;">

		{foreach from=$groups item="group"}
			<tr class="{cycle values="even,odd"}">
				<td nowrap="nowrap" align="left">
					&nbsp;
				  {action action="fegroup:toggle_active:`$group->id`:0}
					<a href="admin.php{url action=$action action1=$lastaction}">
						<img src="buttons/flag_{$group->getActiveState()}.gif" border="0" alt="on" />
					</a>&nbsp;&nbsp;
				  {/action}
				  
				  {action action="fegroup:edit:`$group->id`:"}
					<a href="admin.php{url action=$action action1=$lastaction}">{$group->name}({$group->id})</a>
				  {/action}
				</td>
				<td valign="middle" align="right" class="linebottomlight">
				  {action action="fegroup:edit:`$group->id`:"}
					<a href="admin.php{url action=$action action1=$lastaction}">
						<img src="buttons/edit.gif" border="0" alt="edit" />
					</a>
				  {/action}
				
				  {confirm 	yes="fegroup:delete:`$group->id`:"
							no="fegroup:list::"
							title="Delete Group"
							message="<b>Delete group: `$group->name`?</b>"}
					<a href="admin.php{url action=$action action1=$lastaction}">
						<img src="buttons/delete.gif" border="0" alt="delete" />
					</a>&nbsp;
				  {/confirm}
				</td>
			</tr>
		{/foreach}
		
		{*NEW button*}
		<tr class="bottom">
			<td colspan="2" width="40" nowrap="nowrap" valign="top" align="right">&nbsp;
			  {action action="fegroup:edit:null:"}
				<a href="admin.php{url action=$action action1=$lastaction}">
					<img src="buttons/new.gif" border="0" alt="new" />
				</a>
			  {/action}
			</td>
		</tr>

	</table>


</div>

{include file='footer.tpl'}