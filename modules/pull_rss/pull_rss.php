<?php
/** Support pulling RSS feeds and accessing them from smarty templates */
class Pull_RSS extends Module {
    var $short = "pull_rss";
    var $name  = "Pull RSS from other sites";
    var $register_hooks = array('smarty_config_frontend');
    
    function frontend_interface() { return $this; }
    
    /** Load an RSS-feed into a smarty variable
      *
      * @param url         where to get the feed
      * @param fields      comma-separated list of fields you want to extract from each item, in addition to 'link,title,description,date' who are always extracted
      * @param date_format date-formatting string, preset is 'U', the UNIX epoch.
      * @param var         store feed structure under this name, preset is 'feed'
      * @param limit       limit to this amount of items
      *
      * Example:
      * <code>
      * {pull_rss->feed url=http://rss.nascar.com/rss/news_cup.rss var=NASCAR_NEWSFEED items=999}
      * <H1>NASCAR NEWS:</H1> 
      * {foreach from=$NASCAR_NEWSFEED item=NEWS date=""}
      *    <H2><A HREF="$NEWS.link">{$NEWS.title|upper}</A></H2>
      *    <P>{$NEWS.description|upper}</P>
      * {/foreach}
      * </code>
      */
    function feed($params, $smarty) {
        global $aquarius;
        $feed = new SimplePie();
        $feed->set_timeout(2); // Don't delay for too long
        $feed->set_cache_location($aquarius->cache_path($this->short));
        $feed->set_useragent("SimplePie / Aquarius rev".$aquarius->revision()." ".$this->short);
        
        $feed_url = get($params, 'url');
        $feed->set_feed_url($feed_url);
        $feed->init();
        
        $feed_items = array();
        if ($error = $feed->error()) {
            Log::info("Failed fetching newsfeed $feed_url: ".$error);
        }
        
        $extract_fields = array('title', 'description');
        foreach(array_filter(explode(',', get($params, 'fields'))) as $af) $extract_fields[] = $af;
        $limit = get($params, 'limit', false);
        foreach($feed->get_items() as $i => $feed_item) {
            if ($limit !== false && count($feed_items) >= $limit) break;
            $item = array();
            $item['date'] = $feed_item->get_date(get($params, 'date_format', 'U'));
            $item['link'] = $feed_item->get_permalink();
            foreach($extract_fields as $name) {
                $item[$name] = $feed_item->{'get_'.$name}();
            }
            $feed_items []= $item;
        }
        $smarty->assign(get($params, 'var', 'feed'), $feed_items);
    }

}
