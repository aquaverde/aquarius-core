{include file='header.tpl'}
<h1>Language administration</h1>

<form action="{url action=$lastaction}" method="post">
    <div class="bigbox">
        <div class="bigboxtitle"><h2>Languages</h2></div>
		<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table2">
		{foreach from=$languages item=lang}
			{assign var="nLG" value=$lang->lg}
			{if $lang->active } 
				{assign var="flag" value="_on"}
			{else}
				{assign var="flag" value="_off"}
			{/if}
            {action action="languageAdmin:edit:$nLG"}
              <tr class="{cycle values="even,odd"}">
                    <td nowrap="nowrap" width="25">&nbsp;
                        {action var="active_action" action="languageAdmin:toggle_active:$nLG"}
                        <input class="imagebutton" type="image" name="{$active_action}" src="buttons/flag{$flag}.gif" title="activate/deactivate" alt="activate/deactivate"/>
                        {/action}
                    </td>
                    <td width="25">
                        <b><a href="{url action0=$lastaction action1=$action}">{$lang->lg}</a></b>
                    </td>
                    <td>
                        <a href="{url action0=$lastaction action1=$action}">{$lang->name}</a>
                    </td>
                    <td width="20">
                        <input name="{$action}" class="imagebutton" type="image" src="buttons/edit.gif" alt="edit" title="edit"/>
                    </td>
                        <td width="20">
                            <input name="{$lang->deleteAction}" class="imagebutton" type="image" src="buttons/delete.gif" alt="edit" title="edit"/>
                        </td>
                    <td width="37">
                        <input type="text" name="weight[{$lang->lg}]" value="{$lang->weight}" class="inputweight" tabindex="1" />
                    </td>
                </tr>
                {/action}
                {/foreach}
                <tr class="bottom">
                        <td colspan="6">
                                <form action="?lg=d" method="post">
                                {action action="languageAdmin:edit:null"}
                                        <input name="{$action}" class="imagebutton" type="image" title="new form" src="buttons/new.gif" border="0" alt="new" />
                                {/action}
                                </form>
                        </td>
                </tr>
                <tr class="bottom">
                    <td colspan="6" align="right">
                        <input type="hidden" name="command" value="update-weights">
                        {action action="languageAdmin:setWeighting:null"}
                            <input type="submit" name="{$action}" value="{#s_update_weights#}" class="button" />
                        {/action}
                    </td>
                </tr>
        </table>
    </div>
    
    
</form>
{include file='footer.tpl'}