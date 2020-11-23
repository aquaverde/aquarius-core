{include file='header.tpl'}
<h1>{#mailChimp_menu#}</h1>
<p><a href="https://admin.mailchimp.com/campaigns/" target="_blank">> Link to MailChimp</a><br/></p>
<table class="table">
    <tr>
        <th>Newletter</th>
        <th width="140"  class="right">{#mailChimp_upload#}</th>
    </tr>
    {foreach from=$newsletters item=newsletter}
        <tr>
            <td><b>{$newsletter->title}</b></td>
            <td class="right" style="padding:10px">
                {action action="mailChimp:select_lg:`$newsletter->id`"}
                    <a href="{url action0=$action action1=$lastaction}" title="{#mailChimp_upload#}" class="btn btn-default" style="margin:0">
                        {#mailChimp_upload#}
                    </a>
                {/action}
            </td>
        </tr>
    {/foreach}
    </table>
<br/>
<h1>Kampagnen</h1>
{if isset($apierror)}
	<div style="color: red">{$apierror}</div>
{else}
Anzahl: {$count_campaigns}
<table class="table" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <th>Kampagne</th>
        <th>Status</th>
        <th>Verschickt am</th>
        <th style="text-align:center;">Verschickt an (Personen)</th>
    </tr>
    {foreach from=$campaigns item=campaign}
        <tr>
        <td>{$campaign.settings.title}</td>
        <td>{$campaign.status} </td>
        <td>{$campaign.send_time}</td>
        <td style="text-align:center;"><b>{$campaign.emails_sent}</b></td>
        </tr>
    {/foreach}
</table>
{/if}
{include file='footer.tpl'}
