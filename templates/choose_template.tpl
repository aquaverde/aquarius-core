{include file='header.tpl'}
<h1>{#s_choose_template#}</h1>

<form action="{url}" method="post">
<div class="bigbox">
    <ul style="margin-top: 10px;">
        {foreach from=$options item=option}
            <li>{actionlink action=$option.action title=$option.title button=true show_icon=false}</li>
        {/foreach}
    </ul>
    {actionlink action=cancel button=true}
</div>
</form>
{include file='footer.tpl'}