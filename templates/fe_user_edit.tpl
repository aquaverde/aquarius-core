{include file="header.tpl"}

<form name="userForm" action="{url}" method="post">
	<h1>{if $user->name}{$user->name}{else}{#s_new#}{/if}</h1>
	
	<div class="topbar">
		<h2>{#s_usr_groups#}</h2>

		{foreach from=$groups item="group" }
		  {assign var="id" value=$group->id}
			<input type="checkbox" name="groups2user[]" value="{$group->id}" 
			{if $user->groups[$id] == $group->id }
		  		checked="checked"
		  	{/if}
			/>
			<label for="groups2user[]" class="inline">{$group->name}</label>
			<br/>
		{/foreach}
	
	</div>

	<div id="outer">
		
		<label for="name">{#s_usr_nickname#}</label>
		<input type="text" name="name" value="{$user->name}" class="form-control" maxlength="160"/>

		<label for="new_password">{#s_new_password#}</label>
		<input type="password" name="new_password" value="" class="form-control" maxlength="160"/>
		
		<label for="isActive">{#s_usr_active#}</label>
		<input type="radio" name="isActive" value="1" {if $user->active } checked="checked"{/if}/> {#s_yes#}
		<input type="radio" name="isActive" value="0" {if !$user->active } checked="checked"{/if}/> {#s_no#}		
		
		<br/>
		
		{action action="feuser:save:`$user->id`:"}
		<input type="submit" class="btn btn-primary" name="{$action}" value="{#s_save#}" />
		{/action}

		<input type="submit" name="" value="{#s_cancel#}" class="btn btn-default"/>
			
	</div>
</form>
{include file="footer.tpl"}