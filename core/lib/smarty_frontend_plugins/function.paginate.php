<?php

/** Turn a list of things into a paginated list
  * Params:
  *  var: name of smarty variable to paginate
  *  page: current page (starts at 1)
  *  per_page: items per page (preset: 10)
  *  
  * This function paginates the list stored in the variable given as 'var'
  * parameter and replaces the result into the same variable.
  *
  * In the replacement:
  *   page:       current_page (starts at 1)
  *   page_count: how many pages there are
  *   first, prev, next, last: link parameters to related pages
  *   pages:      list of link parameters to all pages
  *   items:      original content of var cut to items for current page
  *
  * Example, assume the smarty variable $products is an array of strings:
  * 
  * {paginate var=products}
  * <ul>{foreach from=$products item=product}<li>{$product}</li>{/foreach}</ul>
  * {if $products.prev}{link node=$node params=$products.prev}&lt; {wording zurück}{/link}{/if}
  * {foreach from=$products.pages key=p item=pp}
  *   {link node=$node params=$pp} <span {if $p == $products.page}style='font-weight: bold'{/if}>{$p}</span>{/link}
  * {/foreach}
  * {if $products.next}{link node=$node params=$products.next}{wording weiter} &gt{/link}{/if}
  *
  **/
function smarty_function_paginate($params, $smarty) {
    $var = get($params, 'var');
    $list = $smarty->get_template_vars($var);
    $per_page = max(1, intval(get($params, 'per_page', 10)));
    $page_count = ceil(count($list) / $per_page);
    
    // Either read from request or take parameter
    $page = max(1, min($page_count, intval(requestvar('page', get($params, 'page')))));
    
    $items = array_slice($list, $per_page * ($page - 1), $per_page, true);
    
    $pages = array();
    for($p = 1; $p <= $page_count; $p++) {
        $pages[$p] = array('page' => $p);
    }
    
    $nav = compact('page', 'page_count', 'pages', 'items');
    if ($page_count > 0) {
        $nav['first'] = array('page' => 1);
        $nav['last'] = array('page' => $page_count);
        if ($page > 1) {
            $nav['prev'] = array('page' => $page - 1);
        }
        if ($page < $page_count) {
            $nav['next'] = array('page' => $page + 1);
        }
    }
    
    $smarty->assign($var, $nav);
}