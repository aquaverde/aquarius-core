<h1>{#s_idsuche#}</h1>
	
<form name="form1" method="post" action="'.$PHP_SELF.'">
	<table width="300" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td width="4%" align="left" valign="top">
				<img src="picts/corner_top_l.gif" alt="" />
			</td>
			<td width="92%">&nbsp;</td>
			<td width="4%" align="right" valign="top">
				<img src="picts/corner_top_r.gif" alt="" />
			</td>
		</tr>
		<tr>
			<td width="4%">&nbsp;</td>
			<td width="92%">
				<table width="300" border="0" cellpadding="10">
					<tr>
						<td>
							<b>Inhalt suchen</b>
							<br/><br/>
	{*
	if (isset($noContent)) echo '<span style="color:#FF0000">'.$noContent.'</span><br>' ;
	if (isset($updatedId))  echo '<span style="color:#FF0000">Die &Auml;nderungen in der ID \''.$updatedId.'\' wurden gespeichert.</span><br>' ;
	if (isset($comment))   echo '<span style="color:#FF0000">'.$comment.'</span><br>' ;
	*}
	
							{#s_suche_dialog_id#}
							<br/><br/>
							<input type="text" size="10" name="id" />
						</td>
					</tr>
					<tr>
						<td align="right">
							<input type="hidden" name="search" value="1" />
							<input type="submit" name="searchConfirmed" class="submit" value="{#$s_suche#}" />
						</td>
					</tr>
				</table>
			</td>
			<td width="4%" align="right">&nbsp;</td>
		</tr>
		<tr>
			<td width="4%" align="left" valign="bottom">
				<img src="picts/corner_bottom_l.gif" alt="" />
			</td>
			<td width="92%">&nbsp;</td>
			<td width="4%" align="right" valign="bottom">
				<img src="picts/corner_bottom_r.gif" alt="" />
			</td>
		</tr>
	</table>
</form>