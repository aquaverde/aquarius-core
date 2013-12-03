{include file='header.tpl'}
<h1>{#newsletter_send#}</h1>
<div class="bigbox">
    <h2>{#newsletter_choose#}</h2>
    <table border="0" cellpadding="0" cellspacing="0" class="table3">
        <tr><th>{#newsletter#}</th><th style="text-align:center;">{#newsletter_bereits_gesendet#}</th><th style="text-align:center;">{#newsletter_noch_zu_senden#}</th><th style="text-align:center;">{#newsletter_preview#}</th><th style="text-align:right">{#newsletter_send#}…</th></tr>
        {foreach from=$nodelist item=nodeinfo key=index}
            {assign var=parent value=$nodeinfo.node->get_parent()}
            {if $nodeinfo.node->active && $parent->active}
                {if $nodeinfo.depth == 2}
                    {assign var=edition_id value=$nodeinfo.node->id}
                {/if}
                {if $nodeinfo.depth == 1}
                    {if $index > 0 }<tr><td colspan="5">&nbsp;</td></tr>{/if}
                        <tr class="even">
                    {else}
                        <tr{if $sent_counts.$edition_id.notsent == 0} class="dim"{/if}>
                    {/if}
                    <td>
                        {assign var="nodetitle" value=$nodeinfo.node->get_contenttitle()}
                        {if $nodeinfo.depth == 1}
                            <h3>{$nodetitle|strip_tags}</h3>
                        {else}                         
                            {if $sent_counts.$edition_id.notsent == 0}<img src="picts/newsletter_sended.png" alt="{#newsletter_bereits_gesendet#}" title="{#newsletter_bereits_gesendet#}" />&nbsp;{/if}
                            {action action="contentedit:edit:`$nodeinfo.node->id`:`$lg`"}
                                <a href="{url action0 = $action action1 = $lastaction}" title="{#edit#}">{$nodetitle|strip_tags}
                                {if $sent_counts.$edition_id.notsent == 0} ({#newsletter_bereits_gesendet#}){/if}
                                &nbsp;<img src="buttons/edit.gif" title="{#edit#}" alt="{#edit#}"/></a>
                            {/action}
                        {/if}
                    </td>
                    {if $nodeinfo.depth == 2}
                        <td align="center" width="100">
                            {$sent_counts.$edition_id.sent}
                        </td>
                        <td align="center" width="100">
                            {$sent_counts.$edition_id.notsent}
                        </td>
                        <td align="center" >
                            {actionlink action="newsletter:preview:`$nodeinfo.node->id`:$lg"}
                        </td>
                        {if $nodeinfo.node->active == 1}
                            <td align="right">                
                                 {actionlink action="newsletter:presend:`$nodeinfo.node->id`:$lg"}…
                            </td>
                        {/if}
                    {else}
                        <td colspan='4'>&nbsp;</td>
                    {/if}
                </tr>
            {/if}
        {/foreach}
    </table>
</div>
{include file='footer.tpl'}