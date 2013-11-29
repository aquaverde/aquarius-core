{foreach from=$messages item=message}
<div class="alert alert-success {$message->type()}">
    {$message->html()}
</div>
{/foreach}

{if $messagestrs|@count>0}
{strip}
<div class="alert alert-success">
    {foreach from=$messagestrs item=message}
        {$message|escape}<br/>
    {/foreach}
</div>
{/strip}
{/if}