{include file='header.tpl'}

<h1>{#s_groups#}</h1>

<form action="{url action0=$lastaction}" method="post">
<div class="">
	<div class="bigboxtitle"><h2>{#s_groups#}</h2></div>

	<table class="table">

		{foreach from=$groups item="group"}
			<tr>
				<td nowrap="nowrap" align="left">
					&nbsp;
                  {activationbutton action="fegroup:toggle_active:`$group->id`:0" active=$group->active class=imagebutton}
				  &nbsp;&nbsp;
				  {action action="fegroup:edit:`$group->id`:"}
					<a href="admin.php{url action=$action action1=$lastaction}">{$group->name} ({$group->id})</a>
				  {/action}
				</td>
				<td valign="middle" align="right">
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
	</table>
    {action action="fegroup:edit:null:"}
    <a href="admin.php{url action=$action action1=$lastaction}" class="btn btn-sm btn-default btn-success">
        <span class="glyphicon glyphicon-neg glyphicon-plus-sign white"></span> {#s_new#}
    </a>                
    {/action}

</div>
</form>
{include file='footer.tpl'}