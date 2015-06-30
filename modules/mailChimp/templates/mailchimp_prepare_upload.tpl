{include file='header.tpl'}

<div class="bigbox">
<h2>{#mailChimp_upload#}</h2>


    <h1>{#mailChimp_upload#}: {$newsletter->cache_title}</h1>
    <form action="{url action=$lastaction}">
    <h2>1. Sprache wählen</h2>
    <table>
    {foreach from=$langsel key=lg item=lang_content}
    <tr>
        <td><label><input name='newsletter_lg' value='{$lg}' type='radio'/><strong>{$lg}</strong></label></td>
        <td>{$lang_content->title|escape}</td>
        <td class="button">{actionlink action="mailChimp:preview:$lg:`$newsletter->id`" button=true} dd</td>
    </tr>
    {/foreach}
    </table>


<h2>2. Zielliste wählen</h2>
<ul>
{foreach from=$lists item=list}
    <li>{$list.name|escape};{actionlink action="mailChimp:create_campaign:`$newsletter->id`:$list.id" button=true};({$list.default_language})</li>
{/foreach}
</ul>
</form>

</div>
{include file='footer.tpl'}