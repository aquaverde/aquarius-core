{include file='header.tpl'}

<h1>{#export#} {if $form_name}{$form_name}{/if}</h1>


<div class="bigbox">
	<p>{#welche_felder#}</p><br/>
	<form action="{url}" method="post" accept-charset="utf-8">	
		{foreach from=$columntitles key=id item=column}
			<label><input type="checkbox" name="checkboxes_export[]" value="{$id}" class="checkbox" 		{if $cooki_array && !in_array($id,$cooki_array)}{else}checked{/if}>&nbsp;&nbsp;{$column}<label/>
		{/foreach}
		{include file='select_buttons.tpl'}
	</form>
</div>

{include file='footer.tpl'}