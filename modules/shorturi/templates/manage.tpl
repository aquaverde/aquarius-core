{include file='header.tpl'}

{include_javascript file=prototype.js lib=true}
{include_javascript file='module.shorturi.js'}

<h1>{#Shorturi#}</h1><br/>

<form action="{url action=$lastaction}" method="post">

<table cellpadding="0" cellspacing="0" border="0" class="table darker" id="uri_table">
	{assign var="myindex" value=0}

	{foreach from=$uris item=uri}
		{include file='uritable.row.tpl'}
		{assign var="myindex" value=$myindex+1}
	{/foreach}
    
    {assign var="uri" value=""}
    {include file='uritable.row.tpl'}

	<script type="text/javascript">
	<!--
		uri_index = {$myindex};
	// -->
	</script>

</table>

<img src="buttons/new.gif" alt="new" style="margin: 10px 0 0 -1px;padding:0; cursor:pointer;" onclick="add_row_shorturi();" /><br/>

<input type="submit" name="save_button" class="submit" value="{#save#}" />



</form>


{include file='footer.tpl'}