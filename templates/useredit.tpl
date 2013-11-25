{include file='header.tpl'}
<h1>{$title}</h1>


<form action="{url}" name="usrform" method="post">
	<div id="outer">

		<label for="name">{#s_username#}</label>
		<input type="text" name="name" value="{$user->name}" class="form-control"/>

		<label for="password">{#s_new_password#}</label>
		<input type="password" name="user_password" value="" class="form-control"/>

        <fieldset>
            <legend>{#s_permissions#}</legend>
            <label for="">{#s_status#}</label>
            <select name="status">
            {html_options options=$user_statuses selected=$user->status}
            </select>
            <label><input type="checkbox" name="activation_permission" {if $user->activation_permission}checked="checked"{/if} value="1"/>
                {#s_activation_permission#}
            </label>
            <label><input type="checkbox" name="delete_permission" {if $user->delete_permission}checked="checked"{/if} value="1"/>
                {#s_delete_permission#}
            </label>
            <label><input type="checkbox" name="copy_permission" {if $user->copy_permission}checked="checked"{/if} value="1"/>
                {#s_copy_permission#}
            </label>
        </fieldset>
		
        <fieldset>            
            <label for="">{#s_admin_language#}</label>
            <select name="adminLanguage">
                {html_options options=$interfaceLanguages selected=$user->adminLanguage}
            </select>
            <legend>{#s_language#}</legend>
            <label for="defaultLanguage">{#s_default_language#}</label>
            <select name="defaultLanguage">
                {html_options options=$languages selected=$user->defaultLanguage}
            </select>
        </fieldset>

{if $loggedUser->isSiteAdmin() }
        <fieldset>
            <legend>{#s_user_language_access#}</legend>
            {html_checkboxes name='users2languages' options=$languages selected=$selected_languages  separator=' '}
		</fieldset>
    {if $modules|@count}
        <fieldset>
            <legend>{#s_user_module_access#}:</legend>
            {html_checkboxes name='users2modules' options=$modules selected=$selected_modules separator=''}
        </fieldset>
    {/if}
{/if}
		{action action="user:saveUser:`$user->id`"}
			<input type="submit" name="{$action}" value="{#s_done#}" class="btn btn-primary"/>
    	{/action}
    	&nbsp;
    	<input type="submit" name="" value="{#s_cancel#}" class="btn btn-default"/>	
	</div>

{if $nodelist}
{strip}
	<div class="bigbox">
	
		<table id="edit_permissions_table" cellpadding="0" cellspacing="0" width="100%"  border="0" class="table2">
			<tr>
				<th>{#s_may_edit#}</th>
			</tr>
			<tr>
				<td>
					<a href="#" onclick="check_all(getElementById('edit_permissions_table'), true); return false;">Check all</a> /
					<a href="#" onclick="check_all(getElementById('edit_permissions_table'), false); return false;">Uncheck all</a>
				</td>
			</tr>
		{foreach from=$nodelist item=nodeinfo}
        
        	<tr class="">
          		<td nowrap="nowrap">
    		{foreach from=$nodeinfo.connections item=connection}
    	            <img src="picts/{$connection}.gif" alt="" style="vertical-align:middle" />
    		{/foreach}
					{assign var=index value=$nodeinfo.node->id}
          			<input type="checkbox" name="nodeId[]" value="{$nodeinfo.node->id}" class="checkbox"
					{if $availableNodes.$index}
						checked="checked"
					{/if}/>
					&nbsp;{$nodeinfo.node->title}
				</td>
			</tr>
		{/foreach}
		</table>
	</div>
{/strip}
{/if}

{if $nodelist}
    
        <hr>
        {action action="user:saveUser:`$user->id`"}
            <input type="submit" name="{$action}" value="{#s_done#}" class="btn btn-primary"/>
        {/action}
        &nbsp;
        <input type="submit" name="" value="{#s_cancel#}" class="btn btn-default"/>  
{/if}

</form>
{include file='footer.tpl'}
