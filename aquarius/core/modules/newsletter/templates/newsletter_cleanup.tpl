{include file='header.tpl'}
<h1>{#newsletter_subscriptions_cleanup#}</h1>
{if cleanup == 0}
<div class="bigbox">
    <h2>{#newsletter_old_inactive_subscriptions#}</h2>
    {if $totalAddressCount > $addressesPerPage}
        {assign var='pages' value=$totalAddressCount/$addressesPerPage|ceil}
        {section name=pages start=1 loop=$pages+1}
            <a href="{url action0=$lastaction}&page={$smarty.section.pages.index}"><b style="color:orange;">{$smarty.section.pages.index}</b></a> <span class="light">|</span>
        {/section}
    {/if}
<form style="display: inline" name="newsletterForm" method="post" action="{url action1=$lastaction}"  enctype="multipart/form-data">
    <p style="float:right; margin-top:-13px">{$subscriptions_to_cleanup|@count} {#newsletter_addresses#}</p>
<table width="100%" cellpadding="2" cellspacing="0" border="0">
<tr bgcolor="#e0e7ec">
    <td><strong><a href="">{#newsletter_address#}</a></strong></td>
    <td align="center"><strong><a href="" >{#newsletter_language#}</a></strong></td>
    <td align="right"><strong><a href="">{#newsletter_subscription_date#}</a></strong></td>
    <td align="center"><strong><a href="">{#newsletter_subscription_active#}</a></strong></td>
</tr>
{foreach from=$subscriptions_to_cleanup item=addr}
<tr>
    <td>{$addr->address}</td>
    <td align="center">{$addr->language}</td>
    <td align="right">{$addr->subscription_date}</td>
    <td align="center">{$addr->active}</td>
</tr>
{/foreach}
</table>
<p class="spiner">{$subscriptions_to_cleanup|@count} {#newsletter_addresses#}</p>
{confirm yes="newsletter:cleanup:$newsletterRootNode:$lg"
    no=''
    title=$smarty.config.newsletter_cleanup
    message=$smarty.config.newsletter_cleanup_confirm}
    
        <input type="submit" name="{$action}" value="{#newsletter_cleanup#}" class="button" />
{/confirm}
</form>
</div>
{else}
<div class="bigbox">
    <h2>{#newsletter_old_inactive_subscriptions#}</h2>
</div>
{/if}
{include file='footer.tpl'}