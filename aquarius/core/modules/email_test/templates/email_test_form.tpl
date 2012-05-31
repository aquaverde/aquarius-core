{include file='header.tpl'}
<h1>{$lastaction->get_title()}</h1>

<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>Send test emails using multiple methods</h2></div>
    <label>From: <input type="text" name="from" value="{$smarty.post.from|escape}" size="15"/> (optional)</label>
    <label>To:   <input type="text" name="to"   value="{$smarty.post.to|escape}"   size="15"/></label>
    {include file="select_buttons.tpl"}
</div>
</form>
{include file='footer.tpl'}