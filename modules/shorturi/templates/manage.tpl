{include file='header.tpl'}

{include_javascript file=prototype.js lib=true}
{include_javascript file='module.shorturi.js'}

<h1>{#Shorturi#}</h1><br/>

<form action="{url action=$lastaction}" method="post">

<table cellpadding="0" cellspacing="0" border="0" class="table darker" id="uri_table">
	{foreach from=$uris item=uri key=myindex}
		{include file='uritable.row.tpl'}
	{/foreach}
    {include file='uritable.row.tpl' uri=$new_uri myindex=$uris|@count}

	<script type="text/javascript">
	<!--
		uri_index = {$uris|@count};
	// -->
	</script>

</table>

<img src="buttons/new.gif" alt="new" style="margin: 10px 0 0 -1px;padding:0; cursor:pointer;" onclick="add_row_shorturi();" /><br/>

<input type="submit" name="save_button" class="submit" value="{#save#}" />



</form>


{include file='footer.tpl'}