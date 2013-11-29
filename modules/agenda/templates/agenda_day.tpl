{include file='header.tpl'}
<div id="myExit"></div>

{include_javascript file=date-functions.js lib=true}
{include_javascript file=datechooser.js lib=true}

<link rel='stylesheet' href='css/datechooser.css' type='text/css' />

<!--[if lte IE 6.5]>
<link rel='stylesheet' href='css/archiver/select-free.css' type='text/css' />
<![endif]-->

<script type="text/javascript">
Date.monthNames = {$smarty.config.month_names|split:","|@json}
Date.dayNames = {$smarty.config.day_names|split:","|@json}
</script>

<h1>{#day_overview#}</h1>

<form action="{url action=$lastaction}" method="post">
	{assign var=format value=$smarty.const.DATE_FORMAT|replace:'%':''}
	<input class="inputsmall" id="datefield" name="datefield" size="10" maxlength="10" type="text" value=""/>
	<img src="picts/date.gif" onclick="showChooser(this, 'datefield', 'chooserSpan', 1970, 2050, '{$format}', false);"/>
	<div id="chooserSpan" class="dateChooser select-free" style="display: none; visibility: hidden; width: 160px;">
	</div>

	<div>
	{action action="agenda:dayagenda:$lg"}
		<input type="submit" class="submit" name="{$action}" value="{#anzeigen#}"/>
	{/action}
	</div>
</form>

{if $my_data}<br/><br/>
	<h1>{#was_laeuft#}&nbsp;{$my_date}</h1>
	
	<form action="{url action=$lastaction}" method="post">
		<strong>
			{action action="agenda:dayagenda:$lg"}
				<input type="submit" class="submit" name="go_previous" value="{#tag_vorher#}"/>
				<input type="hidden" name="previous_date" value="{$previous_date}">
			{/action}&nbsp;&nbsp;&nbsp;
			{action action="agenda:dayagenda:$lg"}
				<input type="submit" class="submit" name="go_next" value="{#tag_nachher#}"/>
				<input type="hidden" name="next_date" value="{$next_date}">
			{/action}
		</strong>
	</form><br/><br/>
	
	{foreach from=$my_data item=group key=key_data name=data_loop}
		{foreach from=$group item=entry key=key_group name=group_loop}
			{foreach from=$entry item=my_entry key=key_entry name=entry_loop}
				<div style="border-bottom:1px solid lightgrey;margin-bottom:5px;height:50px;">
					{$key_data}:
					{action action="contentedit:edit:`$my_entry->node_id`:`$my_entry->lg`"}
						<strong>
							<a href="{url action0 = $action action1 = $lastaction}" title="Bearbeiten">{$my_entry->title}</a>
						</strong>
					{/action}				
					<p style="font-size:11px;color:grey;float:left;">&nbsp;({$key_group})</p>
				</div>				
			{/foreach}			
		{/foreach}
	{/foreach}
{/if}