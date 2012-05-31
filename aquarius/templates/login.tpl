{extends main.tpl}{block name='content'}
<table>
<td colspan="2" valign="top" class="box0">
	<div class="box4">
		<h1>{$restriction_node->title}</h1>
		{$restriction_node->text1}
{literal}
<script language="javascript" type="text/javascript" src="/md5.js"></script>
<script><!--
    function encryptPassword() {
            var form = document.login;
            form.fe_passwordhash.value = MD5(MD5(form.password.value)+form.challenge.value);
            form.password.value = form.password.value.replace(/./g, '*');
    }
--></script>
{/literal}
		
		<form action="" method="post" id="loginform" name="login" onsubmit="encryptPassword();">
		
			<label for="login">Votre identifiant</label>
			<input type="text" name="fe_username" id="login" class="ef" />
		
			<label for="password">Votre mot de passe</label>
			<input type="password" name="fe_password" id="password" class="ef" />
			<input type="hidden" name="fe_passwordhash" value=""  />
			<input type="hidden" name="challenge" value="{$session_id}"  />
		
			<input type="submit" value="S'identifier" class="Button" />
		
		</form>
	</div>
</td>
<td valign="top">
 
	{if $restriction_node->picture1}
		<img src="/pictures/teaser/alt_{$restriction_node->picture1}" alt="" />
	{/if}

	
</td>
</tr>
</table>{/block}
