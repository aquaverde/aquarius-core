{include file='header.tpl'}
<h1> {if $form_name}{$form_name}{/if}</h1>
<h2>{$lg_desc}</h2>

{literal}
<script type="text/javascript">
    function selectAll(selector) {
        for ( var i = 0 ; i < document.entryForm.length; i++ )
        {
            if (document.entryForm[i].type == "checkbox")
            {
                document.entryForm[i].checked = selector.checked;
            }
        }
    }
</script>
<style>.wrapper{border:none; max-width: none;}</style>
{/literal}

<div>
	{include file='dynform_list_data_commands_top.tpl'}
	{action action="dynform_data:delete_selected:`$form_id`:`$lg`"}
	<form style="display: inline" name="entryForm" method="post" action="{url action0=$lastaction action1=$action}"  enctype="multipart/form-data">
		<div class="table-responsive">
	<table border="0" cellpadding="0" cellspacing="0" class="table table-hover table-condensed">
		<tr class="header">
			<td nowrap="nowrap"><input type="checkbox" onChange="selectAll(this)" class="button"  title="Select all" />&nbsp;</td>
			{foreach from=$columntitles item=title}
				<td nowrap="nowrap"><b>{$title|truncate:80:"..."}</b></td>
			{/foreach}
		</tr>
							
		{foreach from=$records key=id item=entry}
			<tr class="{cycle values='odd,even'}">
				<td nowrap="nowrap">
					<input type="checkbox" class="button" name="sel_records[]" value="{$id}"/>&nbsp;&nbsp;

                        {action action="dynform_data:edit_entry:`$id`:`$lg_filter`"}
                            <a href="{url action0=$lastaction action1=$action}" title="Open/Edit entry"><span class="glyphicon glyphicon-eye-open"></span></a>
                        {/action}
					
				&nbsp;&nbsp;
				{actionlink action="dynform_data:delete_dialog:`$id`:" show_title=false}
				
				</td>
				{foreach from=$entry item=value}
					<td nowrap="nowrap" {if $value|strlen > 80}title="{$value}"{/if}}>{$value|truncate:80:"..."|default:"&nbsp;"}</td>
				{/foreach}
			</tr>
		{/foreach}
	</table>
</div>
	</form>
	{/action}
	<br />
	{include file='dynform_list_data_commands.tpl'}
	
</div>

{include file='footer.tpl'}
