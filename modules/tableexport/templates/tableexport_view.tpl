{include file='header.tpl'}
<h1>{#tableexport_view#}</h1>
<h2></h2>
    <table cellpadding="0" cellspacing="0" border="0" class="table">
        <tr bgcolor="#e0e7ec">
                <th>&nbsp;</th>
            {foreach from=$columns item=col}
                <th>{$col}</th>
            {/foreach}
        </tr>
        {foreach from=$bookings item=booking}
            <tr class="{cycle values="even,odd"}">
                <td nowrap="nowrap">
                    {action action="tableexport:edit:`$booking.$keyfield`"}
                        <a href="{url action0=$lastaction action1=$action}"
                            title="{#s_edit#}">
                        <img src="buttons/edit.gif" title="{#s_edit#}"
                            alt="{#s_edit#}"/>
                        </a>
                    {/action}
                        &nbsp;
                    {confirm yes="tableexport:delete:`$booking.$keyfield`"
                        no=$lastaction
                        title=$smarty.config.s_delete_content
message=$smarty.config.s_confirm_delete_content|sprintf:$booking.$keyfield}
                        <a href="{url action0=$lastaction action1=$action}"
                            title="{#s_edit#}">
                        <img src="buttons/delete.gif"
title="{#s_delete_content#}"
                            alt="{#s_delete_content#}"/>
                        </a>
                    {/confirm}
                    &nbsp;
                </td>
                
                {foreach from=$booking item=col}
                    <td nowrap="nowrap">{if $col !=
""}{$col}{else}&nbsp;{/if}</td>
                {/foreach}
            </tr>
        {/foreach}
    </table>
{include file='footer.tpl'}