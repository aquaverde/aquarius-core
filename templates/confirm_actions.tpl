{include file='header.tpl'}
<h1>{$lastaction->get_title()}</h1>

<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>{$lastaction->get_title()}</h2></div>
    {if $text}<div>{$text}</div>{/if}
{foreach from=$actions item=action}
    {assign var=title value=$action->get_title()|str}
    {if $action}<input type="submit" name="{$action}" value="{$title}" class="btn btn-default" style="margin: 5px 0" /><br/>{/if}
{/foreach}
</div>
</form>
{include file='footer.tpl'}