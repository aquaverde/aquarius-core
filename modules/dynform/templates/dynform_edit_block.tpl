{include file='header.tpl'}
{include file='path.tpl'}

<h1>{dynform_block_name id=$block->id lg=$content->lg}</h1>
 
 <form action="{url}" method="post" id="dynformform">
	<div id="outer">
		<div class='contentedit contentedit_ef'> 
			<label for="name" title="newblockname">{#name#}</label>
			<input class="ef" type="text" name="newblockname" value="{dynform_block_name id=$block->id lg=$content->lg}" id="name"/>
		</div>
		<div class="clear"></div>
		<br />
        {include file='select_buttons.tpl'}
		<br />
	</div>
</form>

{include file='footer.tpl'}
