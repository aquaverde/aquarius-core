{include file='header.tpl'}

<h1>{#newsletter_send#}</h1>
<div class="dialog" style="padding-bottom: 12px;">
    <h2>{$newsletter_name} &rarr; {$edition_title}</h2><br />
	<p>{#newsletter_chose_language#}:</p>
	<form action="{url}" method="post" accept-charset="utf-8">
		{foreach from=$languages item=language}
            <label>
			<input type="checkbox" name="c_languages[]" value="{$language.lg}" id="c_languages_{$language.lg}" 
                {if $language.available}{if $lg == $language.lg}checked{/if}{else}disabled="disabled"{/if} />&nbsp;
                {$language.name}

            <span style="font-size:11px;margin-right:0px;color:#8AA5B8;height:11px;padding:0.1em;">
                {if $language.sent}
                    {#newsletter_bereits_gesendet#}:&nbsp;{$language.sent} {#newsletter_addresses#},&nbsp;
                {/if}
                {if $language.notsent}
                    {#newsletter_noch_zu_senden#}:&nbsp;{$language.notsent}&nbsp;{#newsletter_addresses#}
                {/if}
            </span>
			</label>
        {foreachelse}
            <div>{#newsletter_no_edition_ready#}</div>
		{/foreach}

        {include file=select_buttons.tpl}
	</form>
</div>
<br/>
<h1>{#newsletter_send_test#}</h1>
<div class="dialog" style="padding-bottom: 12px;">
    <h2>{$newsletter_name} &rarr; {$edition_title}</h2><br />

	<form action="{url action0=$lastaction}" method="post">
        {#newsletter_enter_testmail#}:<br/>
        <input type="text" class="ef" name="test_mail" id="test_mail" />
        {foreach from=$available_languages item=language}
            <label>
                <input type="checkbox" name="c_languages[]" value="{$language.lg}" id="c_languages_{$language.lg}" {if $lg == $language.lg}checked{/if} />
                &nbsp;{$language.name}
            </label>
        {foreachelse}
            <div>{#newsletter_no_edition_ready#}</div>
        {/foreach}
        <input class="submit" type="submit" name="{$test_action}" value="{$test_action->get_title()}">
    </form>
</div>

{include file='footer.tpl'}