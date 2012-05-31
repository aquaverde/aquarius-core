{load node=contact var=contact}
<div id="footer">
   	<div class="left">
    	<p><strong>{$contact->company}</strong>, {$contact->street}, {$contact->city}, {$contact->phone}, {$contact->fax}, {$contact->email|email}{edit node=contact}</p>
    </div>
    <ul class="metaNav">
    	{list nodes=contact,impressum}
            <li>{link node=$item.node class=",on"}{$item.content->title}{/link}</li>
        {/list}
    </ul>
</div>