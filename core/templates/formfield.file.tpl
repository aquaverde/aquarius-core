{include_javascript file='contentedit.file.js'}
{include_javascript file='scriptaculous.js' lib=true}
{include_javascript file='tablednd.js' lib=true}

{js}
    document.observe('dom:loaded', function() {ldelim}
        var fileselectors = new FileSelectorList({$field.formfield->id}, {$field.file_row_ids|@json}, {$field.next_id}, '{$field.popup_action|str|urlencode}', {$field.formfield->multi})
        {foreach from=$field.extra_js_includes item=js_file}{include file=$js_file}{/foreach}
    {rdelim})  
{/js}

<table cellpadding="0" cellspacing="0" border="0" class="table darker" id="file_table_{$field.htmlid}">
    {foreach from=$field.value item='fileval'}
        {include file='formfield.file.row.tpl'}
    {/foreach}
</table>

{if $field.formfield->multi}
    {js}
        var table = document.getElementById('file_table_{$field.htmlid}');
        var tableDnD = new TableDnD();
        tableDnD.init(table);
    {/js}
{/if}
