<?php
/** Maintain sitemap index files
  * This module refreshes sitemap index files once a day and sends pings to search engines.
  * The root node may carry a field 'sitemap_exclude' with pointings to nodes (and their children) to be excluded from the sitemap.
  * The config value sitemapxml/exclude_forms can be set to a list of form IDs, nodes using that form will not be included in the sitemap.
  * 
  * The module assumes that pages higher up in the hierarchy are more important.
  * The priority of pages is adjusted accodrdingly. Pages at the first level get
  * 0.8 priority, the next level gets 0.7 and so forth down to 0.5.
  * Content may set the field 'meta_priority' to influence this. This value is
  * set in percent, so meta_priority=90 will yield a priority of 0.9 in the
  * sitemap.
  * 
  */
class Sitemapxml extends Module {

    var $register_hooks = array('daily');

    var $short = "sitemapxml";
    var $name  = "Generate sitemap.xml";

    function initialize($aquarius) {
        parent::initialize($aquarius);
        $this->aquarius = $aquarius;
    }

    function daily() {
        $sitemapper = new AquaSitemapper($this->aquarius);
        $now = time();
        foreach($sitemapper->sitemap_per_host as $host => $sitemap) {
            $name = "aquarius/cache/sitemap-$host.xml";
            $sitemap->save(FILEBASEDIR."$name");
            Log::info("Wrote $name, ".$sitemap->pagecount." pages");

            $this->ping_crawlers($host);
        }
    }

    function ping_crawlers($host) {
        $sitemap_url = new Url('/sitemap.php');
        $sitemap_url->host = $host;

        $ping_urls = array(
            'google' => 'http://www.google.com/webmasters/tools/ping',
//            'yahoo'  => 'http://search.yahooapis.com/SiteExplorerService/V1/ping', // shit doesn't work
            'bing'   => 'http://www.bing.com/webmaster/ping.aspx'
        );

        Log::info("Send ping for $sitemap_url to ".join(', ', array_keys($ping_urls)));

        foreach($ping_urls as $crawler => $ping_url) {
            $url = Url::parse($ping_url);
            $url->add_param('sitemap', $sitemap_url);
            $success = fopen(str($url), 'r');
            if ($success) {
                fclose($success);
            } else {
                Log::warn("Ping to crawler $crawler failed, was using $url");
            }
        }
    }
}


class AquaSitemapper {
    var $make_uri;
    var $available_languages;
    var $sitemap_per_host = array();

    function __construct($aquarius) {
        $this->make_uri = $aquarius->frontend_uri_constructor();
        $this->available_languages = db_Languages::getLanguages(true);

        $root = db_Node::get_node('root');
        
        /* Build filter for nodes to be excluded from search */
        $excluded_filters = array();
        
        $excluded_nodes = $root->get_sitemap_exclude();
        $excluded_forms = $aquarius->conf('sitemapxml/exclude_forms', array());
        
        if (!empty($excluded_nodes)) {
            $excluded_filters []= NodeFilter::create('nodes', $excluded_nodes);
        }
        
        foreach($excluded_forms as $excluded_form_id) {
            $excluded_filters []= NodeFilter::create('form', $excluded_form_id);
        }
        
        // Do not include nodes that fall through to parent
        $excluded_filters []= NodeFilter::create('parent_fallthrough', true);
        
        // Combine the exclusion filters, if one of them matches, the node does not pass
        $node_filter = NodeFilter::create('not',
            NodeFilter::create('or', $excluded_filters)
        );

        // Do not include nodes that are access restricted into the sitemap.
        // The top node is included because that's usually the entry page to the restricted section.
        $descend_filter = NodeFilter::create('access_restricted', false);

        $nodetree = NodeTree::build($root, array('inactive'), $node_filter, $descend_filter);

        NodeTree::walk($nodetree, array($this, 'add_pages'));
    }

    function use_host_sitemap($host) {
        if (!isset($this->sitemap_per_host[$host])) {
            $this->sitemap_per_host[$host] = new SitemapPageDom();
        }
        return $this->sitemap_per_host[$host];
    }

    function add_pages($node) {
        foreach($this->available_languages as $lang) {
            $lg = $lang->lg;
            $content = $node->get_content($lg, true);
            if ($content) {
                $last_update = db_Journal::last_update($content);
                $uri = $this->make_uri->to($content);
                $priority = null;
                if (isset($content->meta_priority) && is_numeric($content->meta_priority)) {
                    // A value between 0 and 1 inclusive?!?
                    // People do not understand ratios.
                    // Thus meta_priority is in percent.
                    // Between 0 and 100, easy!
                    $priority = number_format(min(1, max(0, floatval($content->meta_priority) / 100)), 2);
                } else {
                    switch($node->depth()) {
                        case 0: $priority = '0.8'; break;
                        case 1: $priority = '0.8'; break;
                        case 2: $priority = '0.7'; break;
                        case 3: $priority = '0.6'; break;
                        default: $priority = '0.5';
                    }
                }
                $sitemap = $this->use_host_sitemap($uri->host);
                $sitemap->add_url($uri, null, null, $priority);
            }
        }
    }

}

/** generates sitemap xml page list according to http://www.sitemaps.org/schemas/sitemap/0.9 */
class SitemapPageDom extends SitemapDom {
    var $pagecount = 0;

    /** Create a sitmap dom and initialize 'urlset' root element */
    function __construct() {
        parent::__construct('urlset');
    }

    /** Add an URL to the set
      * Only the first parameter, $loc, is required, the others are optional. If they are left out or null, the corresponding tag will not be added.
      * @param $loc         unescaped URL including http://
      * @param $lastmod     Last modification date, UNIX timestamp
      * @param $changefreq  one of 'always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', or 'never'
      * @param $priority    String between 0.0 and 1.0, or something like that
      */
    function add_url($loc, $lastmod=null, $changefreq=null, $priority=null) {
        $url = $this->createElement('url');

                                  $this->add_child($url, 'loc',        $loc);
        if ($lastmod !== null)    $this->add_child($url, 'lastmod',    date(DateTime::W3C, $lastmod));
        if ($changefreq !== null) $this->add_child($url, 'changefreq', $changefreq);
        if ($priority !== null)   $this->add_child($url, 'priority',   $priority);

        $this->root->appendChild($url);

        $this->pagecount += 1;
    }

}


/** Generates sitemap index file */
class SitemapIndexDom extends SitemapDom {
    var $mapcount = 0;

    /** Create a sitmap dom and initialize 'urlset' root element */
    function __construct() {
        parent::__construct('sitemapindex');
    }

    /** Add a sitemap to the index */
    function add_map($loc, $lastmod) {
        $sitemap = $this->createElement('sitemap');

                                  $this->add_child($sitemap, 'loc',     $loc);
        if ($lastmod !== null)    $this->add_child($sitemap, 'lastmod', date(DateTime::W3C, $lastmod));

        $this->root->appendChild($sitemap);

        $this->mapcount += 1;
    }

}

/** DomDocument extension to create documents suitable for sitemaps */
class SitemapDom extends DomDocument {

    /** Create a sitmap dom and initialize root element */
    function __construct($root_tag) {
        parent::__construct('1.0', 'UTF-8');

        $xmlns_attr = $this->createAttribute('xmlns');
        $xmlns_attr->appendChild($this->createTextNode('http://www.sitemaps.org/schemas/sitemap/0.9'));

        $root = $this->createElement($root_tag);
        $root->appendChild($xmlns_attr);
        $this->appendChild($root);

        $this->root = $root;
    }

    function add_child($parent, $tag, $text) {
        $text_elm = $this->createTextNode($text);
        $elm      = $this->createElement($tag);
        $elm->appendChild($text_elm);
        $parent->appendChild($elm);
    }
}
?>
