<div class="topbar" style="padding: 10px;">
	<div style="float: right"><b>{actionlink action="dynform_data:export:`$form_id`:null" show_title=true}</b></div>
	<div style="margin-bottom: 0; font-size: 1.1em;">
		<a href="#" onclick="document.entryForm.submit(); return false;"><span class='glyphicon glyphicon-trash'></span> {#delete_marked#}</a>
 	</div>
	<div style="margin-top: 30px; font-size: .8em;">
		{if $shown_lg}
		    {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:`$shown_lg`"}
		{else}
		    {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:"}
		    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{#delete_entries#}:&nbsp;&nbsp;
		    {foreach_language}
		        {actionlink action="dynform_data:delete_entries_dialog:`$form_id`:`$entry.lang->lg`" title=$entry.lang->lg}&nbsp;&nbsp;
		    {/foreach_language}
		{/if}
	</div>
</div>


