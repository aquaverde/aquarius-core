{include file='header.tpl'}
	<form name="groupForm" action="{url}" method="post">
		{*// if the group already exists get their name*}
		{if $group->id != 'null'}
			<h1>{#s_usr_group#} {#s_usr_edit#}</h1>		
		{else}
			<h1>{#s_usr_group#} {#s_usr_edit#}</h1>
		{/if}
		
		<div id="outer">
		
			<h2>{#s_usr_groupname#}</h2>
			<input type="hidden" name="groupId" value="{$group->id}" />
			<input type="text" name="groupName" style="width:100%" maxlength="160" value="{$group->name}" />

		</div>
		<div class="bigbox">
		
			<div class="bigboxtitle"><h2>{#s_usr_group_has_access#}</h2></div>

			<table cellpadding="0" cellspacing="0" width="100%"  border="0" class="table2">
				<tr>
					<td>
						<a href="#" onclick="setAllCheckboxes(document.groupForm, true); return false;">{#s_check_all#}</a> / 
						<a href="#" onclick="setAllCheckboxes(document.groupForm, false); return false;">{#s_uncheck_all#}</a>
					</td>
				</tr>
			{foreach from=$nodelist item=nodeinfo}
			    {strip}
				{assign var=index value=$nodeinfo.node->id}
				<tr class="{cycle values="even,odd"}">
					<td nowrap="nowrap">
				{foreach from=$nodeinfo.connections item=connection}
						<img src="picts/{$connection}.gif" alt="" style="vertical-align:middle" />
				{/foreach}
				
				{if $nodeinfo.node->access_restricted}
					<input type="checkbox" name="nodeId[{$nodeinfo.node->id}]" class="checkbox" value="{$nodeinfo.node->id}" 
					{if $restrictions.$index }
						checked="checked"
					{/if}/>
				{/if}
					&nbsp;{$nodeinfo.node->title}
				</td>
				</tr>
				{/strip}
			{/foreach}
			</table>
			
			{action action="fegroup:save:`$group->id`:"}
				<input type="submit" name="{$action}" value="{#s_save#}" class="submit" />
			{/action}
			
			<input type="submit" name="cancel" value="{#s_cancel#}" class="cancel" />
		</div>
	
		
</form>

{include file='footer.tpl'}