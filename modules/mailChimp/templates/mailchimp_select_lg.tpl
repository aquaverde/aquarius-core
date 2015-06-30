{include file='header.tpl'}

<h1>{#mailChimp_upload#}</h1>
<h3>{$newsletter->title}</h3><br>

<div>
    <h2>1. Sprache wählen</h2>
    <form action="{url url=$url}" method="post">
    <table class="table table-condensed">
    {foreach from=$langsel key=lg item=lang_content}
    <tr>
        <td><label><input name='newsletter_lg' value='{$lg}' type='radio'/> <strong>{$lg}</strong></label></td>
        <td>{$lang_content->title|escape}</td>
        <td class="right" style="padding-bottom:15px;">{actionlink action="mailChimp:preview:$lg:`$newsletter->id`" button=true}</td>
    </tr>
    {/foreach}
    </table>
    <input name='' value='zurück' type='submit' class="btn btn-default"/>
    <input name='{$nextaction}' value='weiter' type='submit' class="btn btn-primary"/>
    </form>
</div>

{include file='footer.tpl'}