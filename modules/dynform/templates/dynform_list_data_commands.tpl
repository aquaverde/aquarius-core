<div class="topbar" style="">

<div>{actionlink action="dynform_data:export:`$form_id`:`$lg`"}</div>
{if $shown_lg}
    {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:`$shown_lg`"}
{else}
    {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:"}

    - {#delete_entries#}:&nbsp;|&nbsp;
    
    {foreach_language node=$node}
        {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:`$entry.lang->lg`" title=$entry.lang->lg} |
    {/foreach_language}
{/if}
<div><a href="#" onclick="document.entryForm.submit(); return false;"><img src="buttons/delete.gif" alt="{#delete_entry#}" class="delete" /> {#delete_marked#}</a></div>

</div>