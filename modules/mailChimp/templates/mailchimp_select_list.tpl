{include file='header.tpl'}

<h1>{#mailChimp_upload#}: {$newsletter->cache_title} ({$lastaction->lg})</h1>
<div class="bigbox">
    <form action="{url url=$url}" method="post"  >
    <h2>2. Zielliste wählen</h2>
    <ul>
    {foreach from=$lists item=list}
        <li style="padding:5px 0;"><input type='radio' value='{$list.id|escape}' name='list_id' {if $list.default_language == $newsletter_lg}checked="checked"{/if}>{$list.name|escape}</li>
    {/foreach}
    </ul>
    <input name='' value='abbrechen' type='submit' class="submit"/>
    <input name='{$nextaction}' value='Kampagne erstellen' type='submit' class="submit"/>
    </form>
</div>
{include file='footer.tpl'}