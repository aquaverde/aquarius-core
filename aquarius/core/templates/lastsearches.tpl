{include file='header.tpl'}
<h1>{#menu_lastsearches#} ({$search->lg})</h1>

<div class="bigbox">
    <div class="bigboxtitle"><h2>{#menu_lastsearches#}</h2></div>
{whilefetch object=$search}
    {strip}
        <div class="{cycle values="even,odd"}">
            <span style="padding: 1em">{$search->time|strtotime|date_format}</span>
            {$search->query|escape}
        </div>
    {/strip}
{/whilefetch}
</div>
{include file='footer.tpl'}