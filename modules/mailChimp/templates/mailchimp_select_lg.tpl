{include file='header.tpl'}

<h1>{#mailChimp_upload#}: {$newsletter->cache_title}</h1>
<h2>1. Sprache wählen</h2>
<form action="{url url=$url}" method="post">
<table>
{foreach from=$langsel key=lg item=lang_content}
<tr>
    <td><label><input name='newsletter_lg' value='{$lg}' type='radio'/><strong>{$lg}</strong></label></td>
    <td>{$lang_content->title|escape}</td>
    <td class="button">{actionlink action="mailChimp:preview:$lg:`$newsletter->id`"}</td>
</tr>
{/foreach}
</table>
<input name='' value='zurück' type='submit'/>
<input name='{$nextaction}' value='weiter' type='submit'/>
</form>

{include file='footer.tpl'}