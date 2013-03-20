{include file='header.tpl'}

<h1>{#mailChimp_upload#}</h1>

<div class="bigbox">
    {foreach from=$newsletters item=newsletter}
        <h2>{$newsletter->title}</h2>
        {action action="mailChimp:select_lg:`$newsletter->id`"}
        	<p style="padding:10px 0 20px 0;"><a href="{url action0=$action action1=$lastaction}" class='submit'>{#mailChimp_upload#}</a></p>
        {/action}
    {/foreach}
</div>

<br/>

<h1>Kampagnen (Anzahl: {$count_campaigns})</h1>
    <table class="table" cellspacing="0" cellpadding="0" border="0">
    <tr><th>Kampagne</th><th>Status</th><th>Verschickt am</th><th>Verschickt an (Personen)</th></tr>
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