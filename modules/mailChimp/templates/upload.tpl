{include file='header.tpl'}

<h1>{#mailChimp_upload#}</h1>

<ul>
{foreach from=$newsletters item=newsletter}
<li>
	
		
		<strong>{$newsletter->title}:</strong>

		{action action="mailChimp:select_lg:`$newsletter->id`"}
			<a href="{url action0=$action action1=$lastaction}" class='button'>upload</a>
		{/action}
</li>
{/foreach}
</ul>

<br/><br/>
<h1>Kampagnen</h1>

Anzahl: {$count_campaigns}<br/>
{foreach from=$campaigns item=campaign}
<ul>
	<li><strong>{$campaign.title}</strong> | Status: {$campaign.status} | Verschickt am {$campaign.send_time} an {$campaign.emails_sent} Personen</li>
</ul>
{/foreach}

{include file='footer.tpl'}