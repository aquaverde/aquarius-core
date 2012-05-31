{extends main.tpl}

{block name="main_content"}
<div id="content">
    {usecontent node=not_found}

        <h1>{$title2|default:$title}{edit}</h1>
        {$text}
        <p>{link node=$subsite}<strong>{wording notfound_to_homepage}</strong>{/link}</p>
    {/usecontent}
    </div>
</div>
{/block}
