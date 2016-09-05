{include file="filemgr_header.tpl"}


{if $hasSpinner}
	{include file="spinner.tpl"}
{/if}

	<form name="select_files" action="{url action=$lastaction}" method="post">
	{if $managerStyle == "list"}
        {include file="filemgr_list.tpl"}
	{else}
        {include file="filemgr_browse.tpl"}
	{/if}

{if $hasSpinner}
	{include file="spinner.tpl"}
{/if}

    </form>
    <div>
	</body>
</html>