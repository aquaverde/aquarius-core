{include_javascript file='contentedit.pointing.js' lib=true}
{include_javascript file='contentedit.pointing_legend.js' lib=true}
{include_javascript file='tablednd.js' lib=true}

<table cellpadding="0" cellspacing="0" border="0" class="table darker" 
    id="pointing_table_{$field.htmlid}" data-newurl="{$simpleurl->with_param($field.row_action)}">
    {foreach from=$field.value item='fileval'}
        {include file='formfield.pointing_legend.row.tpl' last=$fileval@last}
    {/foreach}
</table>

{js}
   reInitTableDnD(document.getElementById('pointing_table_{$field.htmlid}'));
{/js}