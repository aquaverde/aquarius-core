{include file='header.tpl'}
<h1>Change echo cookie</h1>

<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle"><h2>Change echo cookie</h2></div>
    <div>Current config: {$current_logger->echolevel}, {$current_logger->firelevel}</div>
    <label for='loglevel' style="display:inline;" >Override echo and fire levels</label>
    <select id='loglevel' name="loglevel" style="display:inline;">
        {html_options options=$logoptions selected=$current_logger->echolevel}
    </select>
    <select id='loglevel' name="firelevel" style="display:inline;">
        {html_options options=$logoptions selected=$current_logger->firelevel}
    </select>
<br/>
    {include file='select_buttons.tpl'}

</div>
</form>
{include file='footer.tpl'}