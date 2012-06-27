<?

/** Create and copy content in various languages
*/

class action_create_content extends AdminAction {

	var $props = array('class', 'command', 'lg');
		
	/** allows siteadmins */
	function permit_user($user) {
		return $user->isSiteadmin();
	}
}

class action_create_content_show extends action_create_content implements DisplayAction {
    function process($aquarius, $request, $smarty, $result) {
        if (!db_Languages::validate_code($this->lg, false)) throw new Exception("Invalid language: '$this->lg'");

        $nodelist = NodeTree::build_flat(db_Node::get_root());
        $node_select_action = Action::make('nodes_select', 'tree', 'parent_id', $this->lg, 'root', false, '', false, false);
        $smarty->assign('node_select_action', $node_select_action);
        $smarty->assign('nodelist', $nodelist);
        $smarty->assign('languages', db_Languages::getLanguages());
        $result->use_template('create_content.tpl');
    }
}

class action_create_content_create extends action_create_content implements ChangeAction {
    function process($aquarius, $post, $result) {
        $node = db_Node::get_node(requestvar('node_id'));
        if (!$node) {
            $result->add_message(AdminMessage::with_line('warn', 'create_content_no_node_selected'));
            return;
        }

        $src_lang = requestvar('src_lang');
        if(empty($src_lang)) {
            $result->add_message(AdminMessage::with_line('warn', 'create_content_no_source_lg_selected'));
        }

        $target_langs = requestvar('target_lang', array());

        $recursive = requestvar('recursive', null);

        $overwrite = false;
        $merge     = false;
        $merge_strategy = requestvar('merge_strategy');
        switch($merge_strategy) {
        case 'overwrite':       $overwrite = true; break;
        case 'merge':           $merge = true;     break;
        case 'ignore_existing': break;
        default: throw new Exception('Missing or invalid merge_Strategy: '.$merge_strategy);
        }

        $visit_count     = 0;
        $overwrite_count = 0;
        $merge_count     = 0;
        $create_count    = 0;

        $nodes = array();
        if(!empty($recursive)) {
            $nodes = NodeTree::build_flat($node);
        } else {
            $nodes[] = array('node'=>$node);
        }
        foreach($nodes as $anode) {
            $visit_count++;
            $node = $anode['node'];
            $source_content = $node->get_content($src_lang);
            if(empty($source_content)) {
                $messages[] = "No content for node \"$node->title\" in the source language";
                continue;
            }

            $source_content->load_fields();
            foreach($target_langs as $target_lang) {
                if($target_lang == $src_lang) {
                    continue;
                }
                $target_content = $node->get_content($target_lang);
                if($target_content) {
                    if ($merge) {
                        Log::debug("Merging fields to lg $target_lang");
                        $target_content->load_fields();
                        $changed = false;
                        foreach($source_content->get_fields() as $name => $value) {
                            if (empty($target_content->$name)) {
                                Log::debug("Copying field $name");
                                $changed = true;
                                $target_content->$name = $value;
                            }
                        }
                        if ($changed) {
                            Log::debug("Saving merged content lg $target_lang");
                            $target_content->save_content();
                            $merge_count++;
                        }
                    } else if ($overwrite) {
                        Log::debug("Overwriting content lg $target_lang");
                        $target_content->delete();
                        $source_content->lg = $target_lang;
                        $source_content->insert();
                        $source_content->save_content();
                        $overwrite_count++;
                    } else {
                        Log::debug("Not touching content lg $target_lang");
                    }
                } else {
                    Log::debug("Cloning content for lg $target_lang");
                    $source_content->lg = $target_lang;
                    $source_content->insert();
                    $source_content->save_content();
                    $create_count++;
                }
            }
        }

        $message = new AdminMessage('ok');
        $message->add_line('create_content_done_n_nodes', $visit_count);
        if ($overwrite)    $message->add_line('create_content_done_n_overwrites', $overwrite_count);
        if ($merge)        $message->add_line('create_content_done_n_merges', $merge_count);
        if ($create_count) $message->add_line('create_content_done_n_creates', $create_count);
        $result->add_message($message);
        
        $result->touch_region(new Node_Change_Notice($node, false, $recursive));
	}
}
?>