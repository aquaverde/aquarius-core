{php}

header("Content-Type: application/xml; charset=utf-8");

{/php}<?xml version="1.0" encoding="utf-8" ?>
<rss version="2.0">
<channel>
    <title>aquaverde.ch ({$lg})</title>
    <link>http://www.aquaverde.ch/</link> 
    <description>{$title}</description>
    <language>{$lg}</language>
    <lastBuildDate>{'r'|date}</lastBuildDate>
{list childsof=3 limit=2}
{list childsof=$entry}
    {if $total < 5}
        {assign var=total value=$total+1}
    <item>
        <title>{$entry->title}</title> 
        <link>http://www.aquaverde.ch{href node=$entry}</link>
        <description>{$entry->text}</description> 
        <pubDate>{'r'|date:$entry->date}</pubDate> 
    </item>
    {/if}
{/list}
{/list}
</channel>
</rss>