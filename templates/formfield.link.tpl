{include_javascript file='contentedit.link.js'}
{include_javascript file='tablednd.js' lib=true}

<table cellpadding="0" cellspacing="0" border="0" class="table darker" id="link_table_{$field.htmlid}">
    {foreach from=$field.value item='fileval'}
        {include file='formfield.link.row.tpl'}
    {/foreach}
</table>

{if $field.formfield->multi}
    {js}
        var table = document.getElementById('link_table_{$field.htmlid}');
        var tableDnD = new TableDnD();
        tableDnD.init(table);
    {/js}
{/if}