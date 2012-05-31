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

    {action action="filemgr:delete_in:$selectedDir:"}
    <p class="action" style="float:right;">{#s_forselection#}:
        <input name="{$action}" type="submit" onClick="return confirm('{$smarty.config.s_delete_files|escape}');" value="{#s_delFileDelete#}" class="button" />
    </p>
    {/action}

{if $hasSpinner}
	{include file="spinner.tpl"}
{/if}

    </form>
	</body>
</html>