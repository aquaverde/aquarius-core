{extends main.tpl}{block name='content'}
<h1>{$content->title}</h1>

{subscribe}
{if $result.displaySubscribe == 1}
<p>{$content->subscription_text}</p>
<form action="" method="post" name="subscriptionform">
    <input type="hidden" name="subscribe" value="1"/>
    {assign var=n value=$content->get_node()}
    {assign var=count value=0}
    {foreach from=$n->children() item=nl}
        {if $nl->active}
            {assign var=count value=$count+1}
        {/if}
    {/foreach}
    {if $count == 1}
        {assign var=nl value=$n->children()}
        <input type="hidden" name="newsletter" value="{$nl[0]->id}"/>
    {else}
        <select name="newsletter">
        {foreach from=$n->children() item=nl}
            {assign var=nlcontent value=$nl->get_content()}
            {$nlcontent->load_fields()}
            <option value="{$nl->id}">{$nlcontent->name}</option>
        {/foreach}
        </select>
    {/if}
    
    <input type="text" name="email" value=""/>
    <input type="submit" value="{wording subscribe}"/>&nbsp;
</form>
{elseif $result.displayUnsubscribe == 1}
<p>{$content->unsubscription_text}</p>
<form action="" method="post" name="unsubscriptionform">
    <input type="hidden" name="unsubscribe" value="1"/>
        {assign var=n value=$content->get_node()}
        {foreach from=$n->children() item=nl}
            {if $nl->id == $smarty.get.nl}
                {assign var=nlcontent value=$nl->get_content()}
                {*$nlcontent->load_fields()*}
                <input type="hidden" name="newsletter" value="{$nl->id}"/>
                <b>{$nl->get_contenttitle()}</b>
            {/if}
        {/foreach}
    <input type="text" name="email" value=""/>
    <input type="submit" value="{wording unsubscribe}"/>&nbsp;
</form>
{else}
    {if $result.subscribing}
        {if $result.subscriptionOK == 1}
            <p>{$content->subscription_thanx}</p>
        {elseif $result.alreadySubscribed}
            <p>{$content->subscription_error_already_subscribed}</p>
        {elseif $result.subscribeEmailInvalid}
            <p>{$content->subscription_error_invalid_email}</p>
        {/if}
    {elseif $result.activating}
        {if $result.activationOK == 1}
            <p>{$content->activation_thanx}</p>
        {else}
            <p>{$content->activation_error}</p>
        {/if}
    {elseif $result.unsubscribing}
        {if $result.unsubscribeOK == 1}
            <p>{$content->unsubscription_thanx}</p>
        {elseif $result.unsubscribingNotSubscribed}
            <p>{$content->unsubscription_error}</p>
        {/if}
    {elseif $result.unsubscribingConfirming}
        {if $result.unsubscriptionConfirmOK == 1}
            <p>{$content->unsubscription_confirm_thanx}</p>
        {/if}
    {/if}
{/if}
{/block}