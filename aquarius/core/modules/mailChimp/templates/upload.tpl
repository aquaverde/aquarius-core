{include file='header.tpl'}

<h1>{#mailChimp_upload#}</h1>

<ul>
{foreach from=$newsletters item=newsletter}
<li>
	
		
		<strong>{$newsletter->title}:</strong>

		{action action="mailChimp:upload_letter:`$newsletter->id`:1:$lg"}
			<a href="{url action0=$action action1=$lastaction}" title="Bearbeiten">upload</a> | 
		{/action}

		{action action="mailChimp:upload_letter:`$newsletter->id`:0:$lg"}
			<a href="{url action0=$action action1=$lastaction}" title="Bearbeiten">anschauen</a>
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