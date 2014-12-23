{include file="header.tpl"}

<form action="{url action=$lastaction}" method="post">
    <h1>{$lastaction->get_title()}</h1>

    <div id="outer">
        <ul>
        {whilefetch object=$edit_messages}
            <li>
            {include file="message_edit.box.tpl" message=$edit_messages}<br/><br/>
            </li>
        {/whilefetch}
            <li>
            {include file="message_edit.box.tpl" message=$empty_message}
            </li>
        </ul>
    {include file=select_buttons.tpl}
	</div>
</form>
{include file="footer.tpl"}