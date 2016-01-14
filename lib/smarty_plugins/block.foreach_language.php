<?php 
/** Execute block once for each language
    * Optional Parameters:
    *    langs: either "all" (default), "other" or a list of languages (like "de,fr"). (only active languages will be used)
    *    var: alternative name of the container for the loop variables, default: 'entry'
    *    node: Cycle through content of this node in each language. (If this node is specified but cannot be loaded, the block content will not be executed)
    *    parent_fallback: Use content of parent node if no content for language is available (default true, if this is false, languages where the node has no content are skipped)
    *    show_single: Show language when there is only one, standard value is true
    *    active_first: put the active language first in the list, preset false.
    *
    * On each iteration, the $entry container is filled with:
    *    lang: The current language instance
    *    lg: The current language code
    *    content: If node given as parameter, this will be the corresponding content in the current language, or the content of the first parent that has some in the current language.
    *    node: The node instance corresponding to the content (due to parent-fallback it might be a parent of the node given as parameter)
    *   first/last: set to true for the first and last entry, respectively
    *    active: entry is for the current language
    *
    * Example:
    *    {foreach_language langs="de,fr,es" node=$somenode}
    *        {link node=$entry.node}go to node {$entry.node->get_title()} in language {$entry.lang->lg}{/link}
    *    {/foreach_language}
    * would be translated to something like:
    *    <a href=".../de/...">go to Ein Titel in language de</a>
    *    <a href=".../fr/...">go to Un titre in language fr</a>
    *    <a href=".../es/...">go to Un título in language es</a>
    *
    * Warning: Do not nest foreach_language blocks. It's silly. (And won't work, due to the use of function static variables.)
    */

// Helper
function make_language_entry($lang, $node, $fallback, $current_lg) {
    $entry = array('lang' => $lang, 'lg' => $lang->lg, 'active' => $lang->lg === $current_lg);
    if (!$node) {
        return $entry;
    } else {
        $content = false;
        $fallback_node = $node;
        do {
            $content = $fallback_node->get_content($lang->lg, true);
            if ($content) {
                $entry['node'] = $fallback_node;
                $entry['content'] = $content;
                return $entry;
            } else {
                $fallback_node = $fallback_node->get_parent();
            }
        } while ($fallback_node && $fallback);
    }
    return false;
}

function smarty_block_foreach_language($params, $content, $smarty, &$repeat) {
    static $entries; // List of languages yet to be displayed
    static $var; // Name of template var

    $is_first = false;

    // On first invocation, build list
    if ($repeat) {
        $is_first = true;

        $langs_in = get($params, 'langs', 'all');
        $var = get($params, 'var', 'entry');
        $use_node = get($params, 'node');
        $active_first = get($params, 'active_first');
        $parent_fallback = (bool)get($params, 'parent_fallback', true);
        $node = false;
        if ($use_node) {
            $node = db_Node::get_node($use_node);
        }
        // Initialize list of languages
        $langs = array();
        $ignore_langs = array();
        switch($langs_in) {
        case 'other':
            $ignore_langs[] = $smarty->get_template_vars('lg');
        case 'all':
            $langs = db_Languages::getLanguages(true);
            break;
        default:
            foreach (explode(',', $langs_in) as $lg) {
                $lang = db_Languages::staticGet($lg);
                if ($lang) $langs[] = $lang;
            }
        }

        $normal_entries = array();
        $head_entries = array();
        if (count($langs) > 1 || get($params, 'show_single', true)) {
            $current_lg = $smarty->getTemplateVars('lg');
            foreach ($langs as $lang) {
                if (!in_array($lang, $ignore_langs) && (!$use_node || $node)) {
                    $entry = make_language_entry($lang, $node, $parent_fallback, $current_lg);
                    if ($entry) {
                        if ($active_first && $entry['active']) {
                            $head_entries []= $entry;
                        } else {
                            $normal_entries[] = $entry;
                        }
                    }
                }
            }
        }
        $entries = array_merge($head_entries, $normal_entries);
    }
    
    // Load next entry
    $entry = array_shift($entries);
    $repeat = (bool)$entry; // Repeat only if we have a language left
    if ($repeat) {
        $entry['first'] = $is_first;
        $entry['last'] = empty($entries);
    }
    $smarty->assign($var, $entry);
    return $content;
}
?>