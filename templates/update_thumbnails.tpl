{include file='header.tpl'}
<h1>{$lastaction->get_title()}</h1>

<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>{$lastaction->get_title()}</h2></div>
    {html_options name=dir values=$dirs output=$dir_names}
    <br/>
    {include file='select_buttons.tpl'}
</div>
</form>
{include file='footer.tpl'}