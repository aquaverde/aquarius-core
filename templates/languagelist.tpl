{include file='header.tpl'}
<h1>Language administration</h1>

<form action="{url action=$lastaction}" method="post">
    <div class="">
        <div class="bigboxtitle"><h2>Languages</h2></div>
		<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table  table-hover">
		{foreach from=$languages item=lang}
			{assign var="nLG" value=$lang->lg}
			{if $lang->active } 
				{assign var="flag" value="_on"}
			{else}
				{assign var="flag" value="_off"}
			{/if}
            {action action="languageAdmin:edit:$nLG"}
              <tr>
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
        </table>

        <form action="?lg=d" method="post">
        {action action="languageAdmin:edit:null"}
            <button type="submit" name="{$action}" class="btn btn-success btn-sm"><span class="glyphicon glyphicon-plus-sign"></span> {#s_new#}</button>
        {/action}
        </form>
        
        <input type="hidden" name="command" value="update-weights">
        {action action="languageAdmin:setWeighting:null"}
            <button type="submit" name="{$action}" class="btn btn-default btn-xs pull-right">{#s_update_weights#}</button>
        {/action}
        
    </div>
    
    
</form>
{include file='footer.tpl'}