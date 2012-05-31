{include file='header.tpl'}
<h1>{#newsletter_addresses#}</h1>
<div class="bigbox">
    <h2>{#newsletter_subscriptions#}</h2>
        <form action="{url action0=$lastaction}" method="post">
        <table border="0" cellpadding="0" cellspacing="0" class="table2">
        {foreach from=$nodelist item=nodeinfo}
                <tr class="{cycle values="even,odd"}">
                    <td nowrap="nowrap">
                        {assign var="nodetitle" value=$nodeinfo.node->get_contenttitle()}
                        {if $nodeinfo.depth == 1}
                            {action action="newsletter:listaddresses:`$nodeinfo.node->id`:$lg"}
                            <a href="{url action0=$lastaction action1=$action}" title="{$nodetitle}: {#newsletter_subscriptions#}">
                                <img class="imagebutton" src="picts/{$nodeinfo.node->icon()}.gif" alt=""/>&nbsp;
                                {$nodetitle|strip_tags}
                            </a>
                            {/action}
                        {/if}
                    </td>
                </tr>
        {/foreach}
        </table>
    </form>
    </div>
    {assign var='boxtitle' value=#newsletter_all_addresses#}
    {assign var='alladdresses' value=true }
    {include file='newsletter_sub_addresses.tpl'}
</div>
{include file='footer.tpl'}