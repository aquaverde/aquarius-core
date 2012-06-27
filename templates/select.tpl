{include file='header.tpl'}
    <h1>{$title}</h1>
    <div class="bigbox">
        {if $lastaction->get_title()}
            <div class="bigboxtitle"><h2>{$lastaction->get_title()}</h2></div>
        {/if}
        <form action="{if $return}{url action=$lastaction}{else}{url}{/if}" method="post">
            {if $message}<div>{$message}</div>{/if}
            {include file='select_buttons.tpl'}
            {if $text}<div>{$text}</div>{/if}
        </form>
        <br/>
    </div>
{include file='footer.tpl'}