{include file='header.tpl'}
<h1>{#dynform_settings#}</h1>

<div class="bigbox">
<br />
	{#possible_options_fields#} <br /><br />
	{action action="dynform_settings:update_option_fields:`$dynform.id`"}
	
	<form action="{url action0=$lastaction action1=$action}" method="post" id="dynform_settings">
 		<input class="ef" type="text" name="option_fields" value="{$options_fields}" id="name"/>
		<input type="submit" name="" value="{#s_save#}" class="submit" />
 	</form>
 	
 	{/action}

</div>

{include file='footer.tpl'}