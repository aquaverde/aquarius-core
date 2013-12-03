{include file='header.tpl'}
<h1>{#s_really_delete#}</h1>
<form action="{$url}" method="post">
	 <table width="10%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFEBCD">
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


        <table width="300" border="0" cellpadding="10" bgcolor="#FFEBCD">
          <tr>
            <td nowrap>
              	{if $count_languages > 1}
			{#s_mess_delete_all_or_one#}
			<br /><br />
		{else}
			{#s_mess_delete_all#} <br /><br />
		{/if}
            </td>
          </tr>
          <tr>
            <td align="right">
              	{if $count_languages > 1}
			<input type="radio" id="radio_one" name="radio_delete" value="one" /><label for="radio_one">{#s_delete_one#} ({$lg})</label><br /><br />
			<input type="radio" id="radio_all" name="radio_delete" value="all" /><label for="radio_all">{#s_delete_all#}</label><br /><br />
		{else}
			<input type="hidden" name="radio_delete" value="all" />
		{/if}

		<input type="submit" name="{$lastaction}" class="submit" value="  {#s_delete#}  " />&nbsp;
		<input type="submit" name="" class="submit" value="  {#s_cancel#}  " />
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
{include file='footer.tpl'}