{include_javascript file='nodetree.js'}
{include_javascript file='nodes_select.js' lib=true}
{include_javascript file='contentedit.pointing.js' lib=true}
{include_javascript file='contentedit.pointing_legend.js' lib=true}
{include_javascript file='tablednd.js' lib=true}

<table cellpadding="0" cellspacing="0" border="0" class="table darker" 
    id="pointing_table_{$field.htmlid}" data-newurl="{$simpleurl->with_param($field.row_action)}"
    data-formfield="{$field.formfield->id}"
    data-htmlid="{$field.htmlid}"
    data-lg="{$content->lg}"
    >
    <tr class="prepend_new_pointing"><td colspan="4"><span class="glyphicon glyphicon-plus"></span></td></tr>
    {foreach from=$field.value item='fileval'}
        {include file='formfield.pointing_legend.row.tpl' last=$fileval@last}
    {/foreach}
</table>

{js}
   reInitTableDnD(document.getElementById('pointing_table_{$field.htmlid}'));
{/js}