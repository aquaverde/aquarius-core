{include file='header.tpl'}
<h1>{#s_r_menu_show#}</h1>

<form action="{url action=$lastaction}" method="post">
<div class="bigbox">
    <div class="bigboxtitle">
        <h2>{#s_r_search_string#}</h2>
		<input type="text" class="ef" id="search_string" name="search_string" value="{$search_string|escape}" style="margin-top: 5px;" />
        {action action="search_replace:search:$lg"}
            <input type="submit" class="submit" name="{$action}" value="{#s_r_search#}" style="margin-top: 10px;"/>
        {/action}
	
	    {if $results}
	        <br /><br />
            <div class="bigboxtitle"><h2>{#s_r_replace_string#}</h2></div>
		        <input type="text" class="ef" id="replace_string" name="replace_string" style="margin-top: 5px;" />
                    {action action="search_replace:replace:$lg"}<input type="submit" class="submit" name="{$action}" value="{#s_r_replace#}" style="margin-top: 10px;"/>{/action}
	        </div>
	    {/if}
</div>	
</form><br />

{if $counter === 0}{#s_r_no_entries#}{/if}

{if $results}
	<strong>{#s_r_how_many#}&nbsp;{$counter}&nbsp;{#s_r_ersetzt#}:</strong>
	
	<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table">
		<tr>
			<th>{#s_r_nodetitel#}</th>
			<th>{#s_r_field_name#}</th>
			<th>{#s_r_field_value#}</th>
		</tr>
	{foreach from=$results item=result key=key name=result_loop}
		<tr class="nohover">
			<td valign="top">
				{action action="contentedit:edit:`$result.nodeId`:`$result.lang`"}
					<a href="{url action0 = $action action1 = $lastaction}" title="{#edit#}">
						<strong>{$result.title|escape}</strong>
					</a>
				{/action}
			</td>
			<td valign="top">{$result.name}</td>
			<td>{$result.value}</td>
		</tr>
	{/foreach}
	</table>
{/if}

{include file='footer.tpl'}