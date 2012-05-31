{extends main.tpl}
{block name='content'}
    <div id="leftCol">
        <h1>{$title2|default:$title}{edit}</h1>
        {$text}
    </div>
{/block}