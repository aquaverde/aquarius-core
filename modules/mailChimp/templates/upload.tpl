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
<div class="bigbox">
    {foreach from=$campaigns item=campaign}
    	<h2>{$campaign.title}</h2>
    	Status: {$campaign.status} an <b>{$campaign.emails_sent}</b> Personen
    	<br>Verschickt am {$campaign.send_time} <br><br></li>
    {/foreach}
</div>

{include file='footer.tpl'}