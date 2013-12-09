{include file='header.tpl'}
<h1>{#s_cms_users#}</h1>

<form action="{url action0=$lastaction}" method="post">
		<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table">
			<tr>
				<th>&nbsp;</th>
				<th>{#s_users#}</th>
				<th>{#s_status#}</th>
                <th>{#s_default_language#}</th>
				<th>{#s_admin_language#}</th>
				<th>&nbsp;</th>
			</tr>
		{foreach item="user" from=$users}
            {action var="editaction" action="user:editUser:`$user->id`"}
			<tr>
				<td width="25">&nbsp;
					{activationbutton action="user:toggle_active:`$user->id`" active=$user->active class=imagebutton}
				</td>
   				<td>
   				    <a href="{url action0=$editaction action1=$lastaction}">{$user->name}</a>
   				</td>
				<td>
					{assign var="index" value=$user->status}
					{$user_statuses.$index}
				</td>
				<td>
					{$user->defaultLanguage|language_name}
				</td>
                <td>
                    {assign var="adminlg" value="s_`$user->adminLanguage`"}
                    {$smarty.config.$adminlg}
                </td>
				<td align="right">
					<button name="{$editaction}" class="btn btn-xs btn-link"><span class="glyphicon glyphicon-pencil"></button>
					&nbsp;
                    {confirm
                        yes="user:deleteUser:`$user->id`"
                        title=$smarty.config.s_delete_user
                        message=$smarty.config.s_delete_user_confirm|sprintf:$user->name
                    }
                    <button name="{$action}" class="btn btn-xs btn-link"><span class="glyphicon glyphicon-trash"></button>

					&nbsp;
                    {/confirm} 
				</td>
			</tr>
            {/action}         
		{/foreach}
		</table>
        {action action="user:editUser:new"}
            <button name="{$action}" type="submit" class="btn btn-default btn-success">
                <span class="glyphicon glyphicon-neg glyphicon-plus-sign white"></span> {#s_new#}
            </button>
        {/action}
</form>
{include file='footer.tpl'}