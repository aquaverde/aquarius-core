<div class="topbar" style="">

<div style="float: right"><b>{actionlink action="dynform_data:export:`$form_id`:`$lg`"}</b></div>
{if $shown_lg}
    {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:`$shown_lg`"}
{else}
    {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:"}
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{#delete_entries#}:&nbsp;&nbsp; 
    
    {foreach_language}
        {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:`$entry.lang->lg`" title=$entry.lang->lg}&nbsp;&nbsp;&nbsp;&nbsp;
    {/foreach_language}
{/if}
<div style="margin-top: 10px;"><a href="#" onclick="document.entryForm.submit(); return false;">
 <b><span class='glyphicon glyphicon-trash'></span> {#delete_marked#}</b></a></div>

</div>