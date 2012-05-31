{include file='header.tpl'}
<h1>Select the preferences for this user</h1>

<form action="{$url}" name="usrform" method="post">
	<div id="outer">
		
	{foreach from=$prefs item="pref"}
		{assign var="id" value=$pref->id}
					<input	type="checkbox" 
							name="choosenPref[]" 
							value="{$pref->id}"
							{if $usersprefs.$id }
								checked="checked"
							{/if}
					/>
					<label for="choosenPref[]" class="inline">{$pref->name}</label>
					<br/><br/>
	{/foreach}
		
	{action action="user:savePrefs:`$user->id`"}
		<input 	type="submit" 
				name="{$action}" 
				value="{#s_save#}" />
	{/action}
		<input 	type="submit" name="" value="{#s_cancel#}" class="cancel"/>
	</div>
</form>

{include file='footer.tpl'}