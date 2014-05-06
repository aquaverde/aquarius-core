{include file='header.tpl'}
<h1>{#s_choose_template#}</h1>

<form action="{url}" method="post">
    <div id="outer">
        <ul>
            {foreach from=$options item=option}
                <li>{actionlink action=$option.action title=$option.title button=true show_icon=false}</li>
            {/foreach}
        </ul>
    </div>
    {actionlink action=cancel button=true}
</form>
{include file='footer.tpl'}