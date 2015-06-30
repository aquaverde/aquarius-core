{include file='header.tpl'}

<h1>{#mailChimp_upload#}</h1>
<h3>{$newsletter->title} ({$lastaction->lg})</h3><br>
<div>
    <form action="{url url=$url}" method="post"  >
    <h2>2. Zielliste wählen</h2>
    <ul>
    {foreach from=$lists item=list}
        <li style="padding:5px 0;"><input type='radio' value='{$list.id|escape}' name='list_id' {if $list.default_language == $newsletter_lg}checked="checked"{/if}> {$list.name|escape}</li>
    {/foreach}
    </ul>
    <input name='' value='abbrechen' type='submit' class="btn btn-default"/>
    <input name='{$nextaction}' value='Kampagne erstellen' type='submit' class="btn btn-primary"/>
    </form>
</div>
{include file='footer.tpl'}