<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
{feeder->load_items in=$feed_parents}
  <title>{$feed.title|escape}</title>
  <link href="{href node=$feed.node}"/>
  <updated>{$feed.updated}</updated>

{foreach from=$feed_items item=item}
  <entry>
    <title>{$item.title|escape}</title>
    <link href="{href node=$item.node}"/>
    <updated>{$item.updated}</updated>
  </entry>
{/foreach}
</feed>