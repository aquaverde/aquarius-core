<?php
/** Backend workhorse, recieves actions as GET or POST parameters, executes them and displays results.
  * @package Aquarius.backend
  */


require '../lib/init.php';
require 'backend.php';

class ActionQueues {
    static function from_request() {
        // Retrieve the requested actions
        return new self(Action::request_actions($_REQUEST));
    }

    var $changes = [];
    var $sides = [];
    var $displays = [];

    function __construct($actions) {
        // Remove duplicates from actions
        $pending = array();
        foreach($actions as $action) {
            $keep = true;
            foreach($pending as $pendingaction) {
                if ($action->equals($pendingaction)) {
                    $keep = false;
                    break;
                }
            }
            if ($keep) {
                $pending []= $action;
                Log::debug("Pushing ".$action->actionstr());
            }
        }

        // Sort them by their sequence
        usort($pending, function($a, $b) { return $b->sequence - $a->sequence; });

        // Sort them into action groups
        foreach($pending as $action) {
            if      ($action instanceof ChangeAction) $this->changes  []= $action;
            else if ($action instanceof SideAction)   $this->sides    []= $action;
            else                                      $this->displays []= $action;
        }
    }

    function inject($action) {
        array_unshift($this->displays, $action);
    }
}
    
try {
    $pending = ActionQueues::from_request();

    if ($pending->changes && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Action $action is a change action which must be sent by POST. It arrived in a ".$_SERVER['REQUEST_METHOD']." request.");
    }

    // Initialize smarty container
    $displayresult = new DisplayResult();
    $smarty = $aquarius->get_smarty_backend_container();
    $smarty->assign('lg', $lg);
    $smarty->assign('primary_lang', db_Languages::getPrimary());
    $smarty->assign('lang', $lg);

    // Perform changes first
    $changeresult = new ChangeResult();
    foreach($pending->changes as $action) {
        Log::debug("Processing ChangeAction $action");
        $action->process($aquarius, clean_magic($_POST), $changeresult);
    }

    $touched_content = false;
    $node_changes = false;
    foreach($changeresult->touched_regions as $region) {
        Cache::clean();
        
        if ($region instanceof Node_Change_Notice) {
            $touched_content = true;
            $change = $region;
            
            // When a change to a node affects its children, the cached fields of that node and its children must be rebuilt
            $change->changed_node->update_cache($change->affects_children);

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
                case 'loader':
                    Log::debug("Deleting frontloader cache");
                    $frontloader->delete_cache();
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

        $aquarius->db->query("UPDATE content SET cache_fields = null;");

        $aquarius->execute_hooks('clear_content_cache');
    }

    foreach($changeresult->inject_actions as $action) {
        Log::debug("Injecting $action");
        $pending->inject($action);
    }

    $change_messages = $changeresult->messages;
    if ($node_changes) {
        $additional_messages = array_flatten($aquarius->execute_hooks('node_changed', $node_changes));
        $change_messages = array_merge($change_messages, $additional_messages);
    }

    // Post/Redirect/Get after changes
    if ($pending->changes) {
        $next_url = clone $url;
        foreach($pending->sides as $act) $next_url->add_param($act);
        foreach($pending->displays as $act) $next_url->add_param($act);

        // HACK stow messages and node changes in the session
        // These should be passed in the URL too but doing so has security
        // implications which I don't want to address right now
        $aquarius->session_set('admin_messages', AdminMessage::process_messages($change_messages));
        $aquarius->session_set('admin_node_change', $node_changes);

        header("HTTP/1.1 303 See Other");
        header("Location: ".$next_url->str());

        flush_exit();
    }

    // See whether there's a SideAction going on
    if ($pending->sides) {
        $action = first($pending->sides);
        if (count($pending->sides) > 1) Log::warn("Multiple (".count($side_actions).") SideActions, processing first only: $action");

        Log::debug("Processing SideAction $action");

        $action->process($aquarius, clean_magic($_REQUEST));

        flush_exit();
    } 

    // Find something to display
    // Some changes may still be performed by legacy and ChangeAndDisplay actions here

    // Loop until we have a template to display
    $lastaction = false;
    $display_actions = $pending->displays;
    while(!$displayresult->template) {
        if ($displayresult->inject_actions) {
            $action = array_shift($displayresult->inject_actions);
        } else {
            $action = array_shift($display_actions);
        }
        if (!$action) throw new Exception("No displayable actions.".($lastaction ? " Last action: $lastaction" : ''));

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
        } else if ($action instanceof FiddlingAction) {
            Log::debug("Fiddling with actions");
            $action->process($aquarius, $display_actions);
        } else {
            Log::debug("Executing legacy action: ".print_r($action,true));
            $result = $action->execute();

            // Process the results of the action
            $displayresult->messages = array_merge($displayresult->messages, get($result, 'messages', array()));
            $own_smarty = get($result, 'smarty'); // Legacy actions build their own containers
            if ($own_smarty) {
                throw new Exception("Legacy actions not supported anymore, update module action ".$action);
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
    
    // Retrieve the messages and the node change that may have been put into the
    // session.
    $pending_messages = $aquarius->session_get('admin_messages');
    $aquarius->session_set('admin_messages', array());

    $pending_node_change = $aquarius->session_get('admin_node_change');
    $aquarius->session_set('admin_node_change', false);

    // Close the session
    // As long as we have the session open, all other requests using this session remain blocked at session_start(). Thus we close as early as possible.
    session_write_close();


    // Read results
    $template = $displayresult->template;
    $messages = array_merge($changeresult->messages, $displayresult->messages);
    $messages = AdminMessage::process_messages($messages); // Converts legacy messages
    if (is_array($pending_messages)) {
        $messages = array_merge($pending_messages, $messages);
    }


    // If the display action requests not to be included in action history, we use use the next history action as last action instead.
    // This means that AJAX requests can still have the same $lastaction and pending actions as the page they are called from.
    if ($displayresult->skip_return) {
        $lastaction = array_shift($display_actions);
    }

    // The rest of the actions are not executed now but stored in the url again
    // A maximum of ten actions are passed to keep the URL from ballooning
    $simpleurl = clone $url; // Save an url that does not contain pending actions
    foreach(array_slice($display_actions, 0, 10) as $pendingaction) {
        $url->add_param($pendingaction->actionstr());
    }


    // Check whether the navig frame should be reloaded
    $navig_reload_node = false;
    if ($pending_node_change) {
        $navig_reload_node = $pending_node_change->changed_node;
        // With structural changes we update the parent node because the node may be new or deleted
        if ($pending_node_change->structural && !$navig_reload_node->is_root()) {
            $navig_reload_node = $navig_reload_node->get_parent();
        }
    }
    $smarty->assign('navig_reload_node', $navig_reload_node);
    
    
    
    $smarty->assign('lastaction', $lastaction);
    $smarty->assign('simpleurl', $simpleurl);
    $smarty->assign('url', $url);
    $smarty->assign('messages', $messages);

    header("Cache-Control: private");
    header('Content-type: text/html; Charset=utf-8');

    Log::debug("Displaying template: ".$template);
    $smarty->display($template);

} catch (Exception $exception) {
    process_exception($exception);
}

flush_exit();
