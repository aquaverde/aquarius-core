{extends main.tpl}
{block name="main-content"}
	<div class="content-block">
		
	<h1>{$title2|default:$title}{edit}</h1>


{$text}

{subscribe}
{if $result.displaySubscribe == 1}
<p>{$content->subscription_text}</p>
<form action="" method="post" name="subscriptionform" class="form-block">
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
        {wording Newsletter}<br/>
        <select name="newsletter">
        {foreach from=$n->children() item=nl}{$nl|var_dump}
            {if $nl->active}
                {assign var=nlcontent value=$nl->get_content()}
                {$nlcontent->load_fields()}
                <option value="{$nl->id}">{$nlcontent->title}</option>
            {/if}
        {/foreach}
        </select><br/>
    {/if}
    
    <br/>{wording E-Mail}<br/>
    <input type="text" name="email" value="" class="checkfield2"/><br/>
    <input type="submit" value="{wording subscribe}" class="button" />&nbsp;
</form>
{elseif $result.displayUnsubscribe == 1} 
<p>{$content->unsubscription_text}</p>
<form action="" method="post" name="unsubscriptionform" class="form-block">
    <input type="hidden" name="unsubscribe" value="1"/>
        {assign var=n value=$content->get_node()}
        {foreach from=$n->children() item=nl}
            {if $nl->id == $smarty.get.nl}
                {assign var=nlcontent value=$nl->get_content()}
                {*$nlcontent->load_fields()*}
                <input type="hidden" name="newsletter" value="{$nl->id}"/>
                {wording E-Mail}<br/>
            {/if}
        {/foreach}
    <input type="text" name="email" value="" class="checkfield2"/><br/>
    <input type="submit" value="{wording unsubscribe}" class="button"/>&nbsp;
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



</div>

{/block}