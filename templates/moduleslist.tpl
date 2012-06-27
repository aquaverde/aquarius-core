{include file='header.tpl'}
<h1>Modules administration</h1>

<form action="" method="post">
	<div class="bigbox">
		<div class="bigboxtitle"><h2>Modules</h2></div>
		
		<table border="0" width="100%" cellpadding="0" cellspacing="0" class="table2">
			<tr>
				<th>&nbsp;</th>
                <th>Shortcut</th>
				<th>Modulname</th>
				<th>&nbsp;</th>
			</tr>
		{foreach from=$modules item="module"}
			<tr class="{cycle values="even,odd"}">
				<td width="25">&nbsp;
			            {activationbutton action="modules:toggle_active:`$module->id`" active=$module->active}
				</td>
                <td>
                    {$module->short}
                </td>
   				<td>
                    {$module->name}
				</td>
			</tr>
		{foreachelse}
			<tr>
				<td>
					<b>no modules found</b>
				</td>
			</tr>
        {/foreach}
		</table>
		
	</div>
</form>
{include file='footer.tpl'}