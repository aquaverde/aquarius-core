{include file='header.tpl'}
<h1>{#edit_entry#} : {$form_name}</h1>

<div class="bigbox">
	{render_form_entry entry_id=$entry_id lg=$lg fallback_lg=$lg_filter}
</div>

{include file='footer.tpl'}
