<?
/** @package Aquarius.frontend
  */

/** Searches words of search string in content title and content_field.
  * <pre>
  * Params:
  *   search: the search string.
  *   lg: Search for content in that language (uses current language by default)
  *   start: index of first node to put into $search.items
  *   length: count of nodes to put into items (max 100)
  *   notfound_recovery: Look in request URL and referrer URL for search strings. This is mainly used on error pages, to give the users options that might interest them.
  *   use: class name of search to use (optional, standard is 'Content_Search')
  *   
  * </pre>
  * If any of the params search, lg, start or length are not specified, the plugin looks for them in the request.
  *
  */
function smarty_function_search($params, &$smarty) {
    global $DB;
    global $lg;

    $class = get($params, 'use', 'Content_Search');
    $search = new $class();
    $search->read($_REQUEST);
    $search->read($params);

    if (get($params, 'notfound_recovery')) {
        $search->search = smarty_function_search_notfound_recovery();
    }

    $smarty->assign('result', $search->find());
}

/** Find search terms for users stranded on a notfound page.
  * Tries to glean words from request and referer URI. */
function smarty_function_search_notfound_recovery() {
    // What could stranded users have been looking for? We don't really know, but there are clues in the URI they sent and the URI where they came from. Most information should be in the URI path and parameter values.
    $words = array();
    foreach (array($_SERVER['REQUEST_URI'], $_SERVER["HTTP_REFERER"]) as $url) {
        $url = Url::parse($url);

        // Split path at common word separators
        foreach(array_filter(split('[/?._ ]', $url->path)) as $word) {
            if (strlen($word) > 2) $words []= $word;
        }

        // Gather parameter values, but ignore parameter keys
        foreach($url->params as $key => $param) {
            $word = $param ? $param : $key;
            if (strlen($word) > 2) $words []= $word;
        }
    }
    return implode(' ', $words);
}
?>