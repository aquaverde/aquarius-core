
{if $myhtml}

	{$myhtml}

{else}

	{include file='header.tpl'}

	<h1>{#mailChimp_upload#}</h1>

	<p>{$upload_result}</p>

	{include file='footer.tpl'}

{/if}