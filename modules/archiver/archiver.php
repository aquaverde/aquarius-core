<?php 
class Archiver extends Module {
        
    var $register_hooks = array('menu_init', 'daily', 'init_form', 'smarty_config', 'smarty_config_backend');
    
    var $short = "archiver";
    var $name  = "Archiver Modul";
    
    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_modules',
            31,
            new Menu(
                'menu_archiver', 
                Action::make('archiver', 'runconfirm','null'),
                false,
                array(new Menu('archiver_archive', Action::make('archiver', 'runconfirm')))
        ));
    }

    function daily() {
        $this->run();
    }
    
    function run() {
        $now = time();
        $archived_nodes = $this->run_archiver($now);
        $this->run_publisher($now, $archived_nodes);
    }
    
    function run_publisher($now, $archived_nodes) {
        global $DB;
        
        $having_publish_date = 0;
        $to_be_published = 0;
        $archived_count = 0;
        $published = 0;
        
        $publishquery = "
            SELECT node.id AS node_id, 
                content_field_value.value AS publish_date
            FROM node
            JOIN content ON node.id = content.node_id
            JOIN content_field ON content.id = content_field.content_id
            JOIN content_field_value ON content_field.id = content_field_value.content_field_id
            JOIN form ON node.cache_form_id = form.id
            JOIN form_field ON form.id = form_field.form_id
            WHERE form_field.type = 'archiver_publish_date' 
            AND form_field.name = content_field.name";
            
        $publishresult = $DB->mapqueryhash("node_id", $publishquery);
        foreach ($publishresult as $id=>$val) {
            // Publish node when publish date has been reached, but not the archiving date
            $publish_date = $val['publish_date'];

            $date_valid = is_numeric($publish_date) && $publish_date != 0;
            $archived = isset($archived_nodes[$id]);
            $publish = $date_valid && $publish_date <= $now && !$archived;
            
            if ($publish) {
                $node = db_Node::get_node($id);
                if (!$node->active) {
                    Log::info("Publishing ".$node->idstr());
                    $node->active = 1;
                    $node->update();
                    $node->update_cache();
                    $published += 1;
                }
            }
            
            $having_publish_date += $date_valid;
            $to_be_published += $publish;
            $archived_count += $archived;
        }
        
        Log::debug("$having_publish_date nodes have publish date, $published newly activated, $to_be_published currently published, $archived_count currently archived");
    }
    
    function run_archiver($now) {
        global $DB;
        $archived = array();
        
        $archivequery = "
            SELECT node.id AS node_id, 
                content_field_value.value AS archive_date, 
                form_field.sup1 AS sup1,
                form_field.sup3 AS sup3,
                form_field.sup4 AS sup4
            FROM node
            JOIN content ON node.id = content.node_id
            JOIN content_field ON content.id = content_field.content_id
            JOIN content_field_value ON content_field.id = content_field_value.content_field_id
            JOIN form ON node.cache_form_id = form.id
            JOIN form_field ON form.id = form_field.form_id
            WHERE form_field.type = 'archiver_archive_date' 
            AND form_field.name = content_field.name";
        
        $archiveresult = $DB->mapqueryhash("node_id", $archivequery);
        
        $archiving_delay = $this->conf('archiving_delay');
        
        $some_changed = false;
        foreach($archiveresult as $id=>$val) {
            $archive_date = $val['archive_date'];

            // Skip invalid dates
            if (!is_numeric($archive_date) || $archive_date == 0) {
                continue;
            }
            
            if ($archiving_delay) {
                $archive_date = strtotime($archiving_delay, $archive_date);
                if ($archive_date === false) throw new Exception("Error applying archiving_delay '$archiving_delay' to date");
            }
            
            if($archive_date <= $now) {
                $node = db_Node::get_node($id);
                $archived[$id] = $node;
                $changed = false;
                if(!empty($val['sup3'])) {
                    
                    //check if node has already been archived
                    //one of the parents is the archiving_node (sup3)
                    $parents = $node->get_parents();
                    $already_archived = false;
                    foreach($parents as $p) {
                        if($p->id == $val['sup3'])
                            $already_archived = true;
                    }
                    if(!$already_archived) {
                        $parent = db_Node::get_node($val['sup3']);
                        Log::info("Archiving node ".$node->get_title()." ($id) to ".$parent->get_title()." ($parent->id)");
                        if(!empty($val['sup4'])) {
                            if($val['sup4'] == 'first') {
                                $childs = $parent->children();
                                $parent = $childs[0];
                            } elseif($val['sup4'] == 'last') {
                                $count = count($parent->children());
                                $childs = $parent->children();
                                $parent = $childs[$count-1];
                            }
                        }
                        $node->parent_id = $parent->id;
                        $changed = true;
                    }
                }
                if($node->active && $val['sup1'] == 1) {
                    Log::info("deactivate archived node ".$node->idstr());
                    $node->active = 0;
                    $changed = true;
                }
                if ($changed) {
                    $some_changed = true;
                    $node->update();
                    $node->update_cache();
                }
            }
        }
        if ($some_changed) {
            db_Node::update_tree_index();
        }
        return $archived;
    }
    
    function init_form($formtypes) {
        // Add archiving and publishing fields
        // Both based on the 'date' form field
        $formtypes->add_formtype(new formtype_date('archiver_archive_date', 'date'));
        $formtypes->add_formtype(new formtype_date('archiver_publish_date', 'date'));
    }

}