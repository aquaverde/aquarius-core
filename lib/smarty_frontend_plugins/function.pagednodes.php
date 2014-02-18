<?php 

/** Load paginated list of nodes
  * Supports the same parameters as the loadnodes function. Additional parameters:
  *   per_page:          how many items to show per page (standard is 10)
  *   page_request_name: Name of the request variable for page selection (standard is 'page')
  *   url_node:          Node where page links will lead (standard is current node)
  *
  * The following smarty variables will be assigned
  *   current_page: Index of the current page
  *   page_links: Pagination links 'prev', 'next', and 'pages'
  *   page_items: Section nodes to show
  *
  * Notes about the implementation: Ugly. It currently uses the paginator class that was written for backend code. In the paginator the page index is zero based. Better than nothing, though.
  *
  * Example:
  *  {pagednodes childrenof=news per_page=5 page_request_name=news_seite}
  *  <ul>
  *    {foreach from=$page_items item=item}
  *      <li>{link node=$item.node}{$item.content->title}{/link}</li>
  *    {/foreach}
  *  </ul>
  *  {if $page_links.pages|@count > 1}
  *    <div class='page_selector'>
  *      {if $page_links.prev}<a href='{$page_links.prev}'>&lt;&nbsp;zurück</a>{/if}
  *      {foreach from=$page_links.pages item=page}<a href='{$page}' {if $page->page eq $current_page}class='active'{/if}>{$page->page+1}</a> {/foreach}
  *      {if $page_links.next}<a href='{$page_links.next}'>vorwärts&nbsp;&gt;</a>{/if}
  *    </div>
  *  {/if}
  */
function smarty_function_pagednodes($params, $smarty) {
    global $DB;
    global $aquarius;


    $smarty->loadPlugin('smarty_function_loadnodes');
    $params['return_list'] = true;
    $nodes = smarty_function_loadnodes($params, $smarty);

    // Prepare URL for paginator links
    $page_request_name = get($params, 'page_request_name', 'page');
    $url_node = db_Node::get_node(get($params, 'url_node', $smarty->get_template_vars('node')));
    if (!$url_node) $smarty->trigger_error("categorycontent, node could not be loaded");
    $basic_url = $aquarius->frontend_url->to($url_node);
    $paged_url = new PagedURL($basic_url, $page_request_name);

    require_once 'lib/paginator.php';
    $paginator = new Paginator($nodes, get($params, 'per_page', 10), requestvar($page_request_name), $paged_url, 'page');

    $smarty->assign('current_page',  $paginator->current_page);
    $smarty->assign('page_links', $paginator->all_actions());
    $smarty->assign('page_items',  $paginator->current_items());
}

/** Throwaway URL wrapper class for paginator use */
class PagedURL {
    var $base_url;
    var $page;
    var $page_request_name;

    function __construct($base_url, $page_request_name) {
        $this->base_url = $base_url;
        $this->page_request_name = $page_request_name;
    }

    function __toString() {
        return str($this->base_url->with_param($this->page_request_name, $this->page));
    }
}
