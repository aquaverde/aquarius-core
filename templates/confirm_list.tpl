{include file='header.tpl'}
<h1>{$title|default:$lastaction->get_title()|escape}</h1>

<form action="{url}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>{$text_top|escape}</h2></div>
    <ul style="margin-top: 10px;">
        {foreach from=$list key=key item=item}
            <li>
                - {$item|escape}
                <input type='hidden' name='list[]' value='{$key|escape}' />
            </li>
        {/foreach}
    </ul>
    <div>{$text_bottom|escape}</div>
    {include file="select_buttons.tpl"}
</div>
</form>
{include file='footer.tpl'}