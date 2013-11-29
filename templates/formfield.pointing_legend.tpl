{include_javascript file='contentedit.pointing_legend.js'}
{include_javascript file='tablednd.js' lib=true}

<table cellpadding="0" cellspacing="0" border="0" class="table darker" id="pointing_table_{$field.htmlid}">
    {foreach from=$field.value item='fileval' name="pls"}
        {include file='formfield.pointing_legend.row.tpl'}
    {/foreach}
</table>

{js}
    var table = document.getElementById('pointing_table_{$field.htmlid}');
    var tableDnD = new TableDnD();
    tableDnD.init(table);
{/js}

{js}
    function add_pointing_ajax_{$field.htmlid}() 
    {ldelim}
        add_pointing_ajax('{$field.formfield->id}', '{$field.htmlid}', '{$content->lg}');
    {rdelim}
{/js}