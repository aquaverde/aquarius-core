{include file='header.tpl'}
{include file='path.tpl'}
 
<h1>{#delete_field#}</h1>
<div class="dialog">
<form action="{url}" method="post" id="dynformform">
	<div>{#delete#}: <b>{dynform_field_name id=$field->id lg=$lg}</b></div>
	<br />
	{#delete_field_msg#}
	<br />
	{action action="dynform:deletefieldsubmit:`$content->id`:`$lg`:`$node->id`:`$block->id`:`$field->id`"}
		<input type="submit" name="{$action}" class="submit" value="  {#yes#}  " />
	{/action}

    <input type="submit" name="" value="  {#no#}  " class="cancel" />
</form>
</div>
 
{include file='footer.tpl'}