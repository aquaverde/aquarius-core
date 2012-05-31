{include file='header.tpl'}
<h1>{$mod->name}</h1>
{include file='messages.tpl'}

<form action="{url}" name="modform" method="post">
	<div id="outer">
		
		<input type="hidden" name="active" value="1" {$checkActive}/>
		
		<label for="name">Modulename</label>
		<input type="text" name="name" value="{$mod->name}" class="ef"/>
		
		<label for="short">Shortcut</label>
		<input type="text" name="short" value="{$mod->short}" class="ef"/>

	{action action="modules:save:$id"}
		<input 	type="submit" name="{$action}" value="{#s_save#}" class="submit"/>
	{/action}
	
		<input 	type="submit" name="" value="{#s_cancel#}" class="cancel"/>
	
		
		</div>
</form>

{include file='footer.tpl'}