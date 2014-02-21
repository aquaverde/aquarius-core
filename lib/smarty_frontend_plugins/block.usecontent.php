<?php
/** Temporarly load other content fields
  * Params:
  *   node: The thing you want to load fields for
  *   lg: If you wish to use a specific language
  *   check_access: Check that the user has access to the content, preset: true.
  * The fields of the given content are loaded and cover the existing variables (they will be restored when the block finishes. Manual scoping, you gotta love it.)
  * If the user does not have permission to access the node (access_restriction) or the node could not be loaded, the content of the block is not displayed.
  * Example:
  * {usecontent node="23"}Look! The title of node 23: {$title}{/usecontent}
  *
  * Other example:
  * {list nodes=$pointers}
  *   {usecontent node=$entry}
  *     - {link node=$entry}{$title} ({$text|truncate:30}){/link}
  *   {/usecontent}
  * {/list}
  */
function smarty_block_usecontent($params, $content, &$smarty, &$repeat) {
    static $replace_stack = array(); // Stack containing the replaced values
    
    if ($repeat) {
        /* Start of block */
        $load = true;
        $reason = false;
        
        // Load node and content
        $nodestr = get($params, 'node');
        $node = db_Node::get_node($nodestr);
        $lg = get($params, 'lg', $smarty->get_template_vars('lg'));
        $content = false;

        if ($node) {
            // Refuse to load nodes that are not active themselves.
            // Since we're loading on request, the assumption is that
            // we may show content even if parent nodes are disabled
            // This helps in cases where you have 'auxiliary nodes' under
            // a deactivated node
            if ($smarty->require_active && $node->active == false) {
                $load = false;
                $reason = "Node ".$node->idstr()." is not active";
            }
        
            // Check permissions
            if (get($params, 'check_access', true)) {
                $restriction_node = $node->access_restricted_node();
                if ($restriction_node) {
                    $access = false;
                    $user = db_Fe_users::authenticated();
                    if ($user) $access = $user->hasAccessTo($restriction_node->id);

                    if (!$access) {
                        $load = false;
                        $reason = "User does not have access to $restriction_node->id";
                    }
                }
            }

            // Load the content
            $content = $node->get_content($lg);
            if (!$content) {
                $load = false;
                $reason = "No content for node $node->id in language $lg";
            } elseif ($smarty->require_active && $content->active == false) {
                $load = false;
                $reason = "Content for node $node->id in language $lg not active";
            }
        } else {
            $load = false;
            $reason = "Could not load node for '$nodestr'";
        }

        if ($load) {
            $replace_stack []= $smarty->get_template_vars();
            $smarty->assign($content->get_fields());
        } else {
            Log::debug("Usecontent block not executed: $reason");
            $repeat = false;
        }
    } else {
        /* End of block */
        // HACK galore
        $smarty->clearAllAssign(); // Clean the plate
        $smarty->assign('smarty', $smarty); // Smarty needs itself in the variables
        $smarty->assign(array_pop($replace_stack)); // Now put back what was there before
    }
    return $content;
}