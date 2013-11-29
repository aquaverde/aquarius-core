{include file='header.tpl'}
<form method="post" name="userAdmin" action="{url}">
	<h1>{#s_usr_user#} {#s_usr_manage#}</h1>
	
	<div class="topbar">
	
	<h2>{#s_filter#}</h2>

	<table border="0">
		<tr>
			<td width="200">{#s_username_contains#}</td>
			<td>{#s_user_of_goup_only#}</td>
		</tr>
		<tr>
			<td>
				<input type="text" name="user_search" value="{$user_search}" class="form-control" />
			</td>
			<td>
				<select name="group_search" >
						<option value=""> {#s_all#} </option>
						
				{foreach from=$groups item="group" }
					<option value="{$group->id}" 
						{if $group_search == $group->id} selected="selected" {/if}
					>{$group->name}</option>
				{/foreach}
				</select>
			</td>
			
		</tr>
		<tr>
			<td colspan="2">
				<br/>
				{action action="feuser:list::"}
					<input type="submit" name="{$action}" value="{#s_search#}" class="btn btn-sm btn-default" />
				{/action}
				{action action="feuser:list:::filter_reset"}
				    <input type="submit" name="{$action}" value="{#s_filter_reset#}" class="btn btn-sm btn-default" />
				{/action}
				{action action="feuser:export::"}
                    <input type="submit" name="{$action}" value="{#s_export#}" class="btn btn-sm btn-default" />
				{/action}
			</td>
		</tr>
	</table>
	
	</div>

{if $hasSpinner}
	{include file="spinner.tpl"}
{/if}

	<div class="">
		<div class="bigboxtitle"><h2>{#s_users#} (Id)</h2></div>

		<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table" style="margin-top:10px;">

			{foreach from=$users item="user" key="index"}
			
			<tr>
				<td nowrap="nowrap">
					&nbsp;
				  {action action="feuser:toggle_active:`$user->id`:"}
					<a href="{url action=$action action1=$lastaction}">
						<img src="buttons/flag_{$user->getActiveState()}.gif" border="0" alt="on" />
					</a>&nbsp;&nbsp;
				  {/action}
				  {action action="feuser:edit:`$user->id`:"}
					<a href="{url action=$action action1=$lastaction}" title="ID {$user->id}">
						{$user->name} <img src="buttons/edit.gif" border="0" alt="edit" />
					
					</a>
				  {/action}
				 
				</td>

				{foreach from=$user_action_generators item='action_generator'}
					<td>
					{assign var='action' value=$action_generator->get_action($user)}
					{if $action}
					<a href="{url action=$action action1=$lastaction}">
					{$action->get_title()} <img src="buttons/edit.gif" border="0" alt="edit" />
					</a>
					{/if}
					
					</td>
				{/foreach}

				<td width="40" valign="middle" align="right">
				  {confirm 	yes="feuser:delete:`$user->id`:"
								no=""
								title="Delete User"
								message="Really delete this user?"}
					<a href="{url action=$action action1=$lastaction}">
						<img src="buttons/delete.gif" border="0" alt="delete" />
					</a>&nbsp;
				  {/confirm}
				</td>
			</tr>

			{/foreach}
		</table>

        {action action="feuser:edit:null:"}
            <a href="{url action=$action action1=$lastaction}" class="btn btn-sm btn-default btn-success">
            <span class="glyphicon glyphicon-neg glyphicon-plus-sign white"></span> {#s_new#}
            </a>
        {/action}
        
	{if $hasSpinner}
		{include file="spinner.tpl"}
	{/if}

	</div>
	
</form>
{include file='footer.tpl'}