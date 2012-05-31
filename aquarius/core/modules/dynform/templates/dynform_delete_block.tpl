{include file='header.tpl'}
{include file='path.tpl'}
 
<h1>{#delete_block#}</h1>
<div class="dialog">
<form action="{url}" method="post" id="dynformform">
	<div>{#delete#}: <b>{dynform_block_name id=$block->id lg=$lg}</b></div>
	<br />
	{#delete_block_msg#}
	<br />
	{action action="dynform:deleteblocksubmit:`$content->id`:`$content->lg`:`$node->id`:`$block->id`:0"}
		<input type="submit" name="{$action}" class="submit" value="  {#yes#}  " />
	{/action}

    <input type="submit" name="" value="  {#no#}  " class="cancel" />

</form>
</div>
 
 
 
 {include file='footer.tpl'}