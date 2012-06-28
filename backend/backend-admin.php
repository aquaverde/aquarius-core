<?php
/** Backend workhorse, recieves actions as GET or POST parameters, executes them and displays results.
  * @package Aquarius.backend
  */


require 'backend.php';

require 'lib/AdminMessage.php';

try {

    // Retrieve the requested actions
    $requestactions = Action::request_actions($_REQUEST);

    // Sort them by their sequence
    usort($requestactions, create_function('$a, $b', 'return $b->sequence - $a->sequence;'));

    // Remove duplicates from remaining pending actions and restrict to max 10
    $pendingactions = array();
    foreach($requestactions as $requestaction) {
        $keep = true;
        foreach($pendingactions as $pendingaction) {
            if ($requestaction->equals($pendingaction)) {
                $keep = false;
                break;
            }
        }
        if ($keep) {
            $pendingactions[] = $requestaction;
            Log::debug("Pushing ".$requestaction->actionstr());
        }
        if (count($pendingactions) >= 10) break;
    }

    // Sort them into action groups
    $change_actions = array();
    $side_actions = array();
    $display_actions = array();
    foreach($pendingactions as $action) {
        if      ($action instanceof ChangeAction)            $change_actions[] = $action;
        else if ($action instanceof SideAction)              $side_actions[] = $action;
        else                                                 $display_actions[] = $action;
    }
    
    // Initialize smarty container
    $displayresult = new DisplayResult();
    $smarty = $aquarius->get_smarty_backend_container();
    $smarty->assign('lg', $lg);
    $smarty->assign('primary_lang', db_Languages::getPrimary());
    $smarty->assign('lang', $lg);

    // Perform changes first
    $changeresult = new ChangeResult();
    foreach($change_actions as $action) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            throw new Exception("Action $action is a change action which must be sent by POST. It arrived in a ".$_SERVER['REQUEST_METHOD']." request.");
        Log::debug("Processing ChangeAction $action");
        $action->process($aquarius, clean_magic($_POST), $changeresult);
    }

    $touched_content = false;
    $node_changes = false;
    foreach($changeresult->touched_regions as $region) {
        if ($region instanceof Node_Change_Notice) {
            $touched_content = true;
            $change = $region;
            // When a change to a node affects its children, the cached fields of that node and its children must be rebuilt

            if ($change->affects_children) $change->changed_node->update_cache();

            // When the tree structure is changed, the index fields must be rebuilt
            if ($change->structural) db_Node::update_tree_index();

            if (!$node_changes) {
                $node_changes = $change;
            } else {
                $node_changes = $node_changes->merge($change);
            }
        } else {
            switch ($region) {
                case 'content':
                    $touched_content = true;
                    break;
                case 'db_dataobject':
                    $GLOBALS['_DB_DATAOBJECT']['CACHE'] = array();
                    $GLOBALS['_AQUARIUS_CONTENT_CACHE'] = array();
                    break;
                default:
                    throw new Exception("Don't know what to do about touched region '$region'");
            }
        }
    }

    if ($touched_content) {
        Log::debug("Clearing smarty frontend cache");
        $smarty_frontend = $aquarius->get_smarty_frontend_container(false);
        $smarty_frontend->clear_all_cache();
    }

    foreach($changeresult->inject_actions as $action) {
        Log::debug("Injecting $action");
        array_unshift($display_actions, $action);
    }

    // See whether there's a SideAction going on
    if (!empty($side_actions)) {
        $action = first($side_actions);
        if (count($side_actions) > 1) Log::warn("Multiple (".count($side_actions).") SideActions, processing first only: $action");

        Log::debug("Processing SideAction $action");

        $action->process($aquarius, clean_magic($_REQUEST));

        flush_exit();
    } 

    // Find something to display
    // Some changes may still be performed by legacy and ChangeAndDisplay actions here

    // Loop until we have a template to display
    $lastaction = false;
    while(!$displayresult->template) {
        $action = array_shift($display_actions);

        if (!$action) throw new Exception("No displayable actions.".($lastaction ? " Last action: $lastaction" : ''));
        //if (!$action) $action = Action::make('nodetree', 'show', $lg, 'sitemap');

        // Check permissions (they are checked on construction, but something could have changed)
        if (!$action->permit()) {
            Log::info("Ignoring ".str($action)." because permit() changed its mind since construction.");
            continue; // Next action, please
        }

        if ($action instanceof DisplayAction) {
            Log::debug("Processing DisplayAction $action");
            $action->process($aquarius, clean_magic($_REQUEST), $smarty, $displayresult);
            $lastaction = $action;
        } else if ($action instanceof ChangeAndDisplayAction) {
            Log::debug("Processing ChangeAndDisplayAction $action");
            $action->process($aquarius, clean_magic($_POST), $smarty, $changeresult, $displayresult);
            $lastaction = $action;
        } else {
            Log::debug("Executing legacy action: ".print_r($action,true));
            $result = $action->execute();

            // Process the results of the action
            $displayresult->messages = array_merge($displayresult->messages, get($result, 'messages', array()));
            $own_smarty = get($result, 'smarty'); // Legacy actions build their own containers
            if ($own_smarty) {
                $smarty = $own_smarty;
                $displayresult->template = $smarty->tmplname;
                $smarty->assign('lg', $lg);
                $smarty->assign('primary_lang', db_Languages::getPrimary());
                $smarty->assign('lang', $lg);
            }

            // See if execution yielded another action to be executed
            $nextaction = get($result, 'action');
            if ($nextaction) {
                // Put that action in front of the queue
                array_unshift($display_actions, $nextaction);
            }

            $lastaction = $action;
        }
    }
    
    // Close the session
    // As long as we have the session open, all other requests using this session remain blocked at session_start(). Thus we close as early as possible.
    session_write_close();

    // Read results
    $template = $displayresult->template;
    $messages = array_merge($changeresult->messages, $displayresult->messages);

    if ($node_changes) {
        $additional_messages = array_flatten($aquarius->execute_hooks('node_changed', $node_changes));
        $messages = array_merge($messages, $additional_messages);
    }

    // If the display action requests not to be included in action history, we use use the next history action as last action instead.
    // This means that AJAX requests can still have the same $lastaction and pending actions as the page they are called from.
    if ($displayresult->skip_return) {
        $lastaction = array_shift($display_actions);
    }

    // The rest of the actions are not executed now but stored in the url again
    $simpleurl = clone $url; // Save an url that does not contain pending actions
    foreach($display_actions as $pendingaction)
        $url->add_param($pendingaction->actionstr());

    // Process legacy messages into strings
    $messagestrs = array();
    $proper_messages = array();
    foreach($messages as $message) {
        if ($message instanceof AdminMessage) {
            $proper_messages []= $message;
        } else {
            if (is_array($message)) {
                $message[0] = $smarty->get_config_vars($message[0]);
                $str = call_user_func_array('sprintf', $message);
            } elseif (is_object($message)) {
            $str = str($message); 
            } else {
                $str = $smarty->get_config_vars($message);
                if (empty($str)) $str = $message;
            }
            Log::message($str);
            $messagestrs[] = $str;
        }
    }

    // Check whether the navig frame should be reloaded
    $navig_reload_node = false;
    if ($node_changes) {
        $navig_reload_node = $node_changes->changed_node;
        // With structural changes we update the parent node because the node may be new or deleted
        if ($node_changes->structural && !$navig_reload_node->is_root()) {
            $navig_reload_node = $navig_reload_node->get_parent();
        }
    }
    $smarty->assign('navig_reload_node', $navig_reload_node);
    
    
    
    $smarty->assign('lastaction', $lastaction);
    $smarty->assign('simpleurl', $simpleurl);
    $smarty->assign('url', $url);
    $smarty->assign('messages', $proper_messages);
    $smarty->assign('messagestrs', $messagestrs);

    // Tell the browser that admin pages are valid for the day before yesterday which prevents the browser from caching the page
    header("Cache-Control: private");
    header("Expires: " . gmdate("D, d M Y H:i:s", time() - 60 * 60 * 24) . " GMT");
    header('Content-type: text/html; Charset=utf-8');

    Log::debug("Displaying template: ".$template);
    $smarty->display($template);

} catch (Exception $exception) {
    process_exception($exception);
}

flush_exit();
?>