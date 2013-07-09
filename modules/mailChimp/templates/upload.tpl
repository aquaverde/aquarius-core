{include file='header.tpl'}
<h1>{#mailChimp_menu#}</h1>
<table class="table" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <th>Newletter</th>
        <th width="140" style="text-align:center;">{#mailChimp_upload#}</th>
    </tr>
    {foreach from=$newsletters item=newsletter}
        <tr>
            <td><b>{$newsletter->title}</b></td>
            <td align="center" style="padding:10px">{action action="mailChimp:select_lg:`$newsletter->id`"}<a href="{url action0=$action action1=$lastaction}" class='submit'>{#mailChimp_upload#}</a>{/action}</td>
        </tr>
    {/foreach}
    </table>
    <p><br/><a href="https://us7.admin.mailchimp.com/campaigns/">> Link to MailChimp</a></p>
<br/>
<h1>Kampagnen (Anzahl: {$count_campaigns})</h1>
<table class="table" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <th>Kampagne</th>
        <th>Status</th>
        <th>Verschickt am</th>
        <th>Verschickt an (Personen)</th>
    </tr>
    {foreach from=$campaigns item=campaign}
        <tr>
        <td>{$campaign.title}</td>
        <td>{$campaign.status} </td>
        <td>{$campaign.send_time}</td>
        <td><b>{$campaign.emails_sent}</b></td>
        </tr>
    {/foreach}
</table>
{include file='footer.tpl'}