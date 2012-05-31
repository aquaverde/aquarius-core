{foreach from=$messages item=message}
<div class="message {$message->type()}">
    {$message->html()}
</div>
{/foreach}

{if $messagestrs|@count>0}
{strip}
<div class="message">
    {foreach from=$messagestrs item=message}
        {$message|escape}<br/>
    {/foreach}
</div>
{/strip}
{/if}