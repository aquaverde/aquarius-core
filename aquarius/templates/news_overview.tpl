{extends main.tpl}
{block name='content'}    
    <div id="leftCol">
        <h1>{$title2|default:$title}{edit}</h1>
        <ul class="news">
            {list childrenof=news}
                <li>
                    <a name="item{$item.node->id}"></a>
                    {if $item.content->date}<span class="date">{$item.content->date|date_format}</span>{/if}
                    <h3>{$item.content->title}{edit node=$entry}</h3>
                    {$item.content->text}
                </li>
            {/list}
        </ul>
    </div>
{/block}