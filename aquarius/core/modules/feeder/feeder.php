<?php

/** Generate Newsfeeds from site content
  * 
  * The feed is built primarly from the journal table. Content having a 'date'
  * field will be included with that date. Only content nodes (nodes
  * in boxes) are included, it is assumed that other nodes are boring.
  * 
  * Param:
  *   in: Restrict to nodes below these nodes
  * 
  * Example:
  *   {feeder->load_items in=products,news,events}
  * 
  * Inactive entries and access-restricted entries are not included in the feed.
  * 
  */
class Feeder extends Module {
    var $short = "feeder";
    var $name  = "Generate an newsfeed from site content";
    
    var $register_hooks = array('smarty_config_frontend');
            
    function frontend_interface() {
        return $this;
    }
    
    /** Load list of nodes from the edit-journal.
      * 
      * Param:
      *   in:    Restrict to nodes below these nodes. Preset: all nodes.

      * Only content nodes (nodes in boxes) are included, it is
      * assumed that other nodes are boring.
      * 
      * Two smarty variables are assigned:
      *   feed:       title, updated and node fields
      *   feed_items: entry for each node with title, updated and node 
      * 
      * 
      * Example:
      *   {feeder->load_items in=products,news,events}
      * 
      * Inactive entries and access-restricted entries are not included in the
      * list.
      * 
      */
    function load_items($params, $smarty) {
        global $DB;
        
        $lg = $smarty->get_template_vars('lg');
        
        $only_in = get($params, 'in', null);
        $parents_restriction = '1=1';
        if ($only_in !== null) {
            $only_in_nodes = db_Node::get_nodes($only_in);
            if (count($only_in_nodes) > 0) {
                $r = array();
                foreach($only_in_nodes as $n) $r []= '(node.cache_left_index > '.$n->cache_left_index.' AND node.cache_right_index < '.$n->cache_right_index.')';
                $parents_restriction = '('.join('OR', $r).')';
            } else {
                // No parents selected? Select nothing.
                $parents_restriction = '1=0';
            }
        }
        
        
        // find content nodes
        $last_updates = $DB->queryhash("
            SELECT content_id, MAX(journal.last_change) AS last_change
            FROM journal
            JOIN content ON journal.content_id=content.id
            JOIN node ON content.node_id=node.id
            WHERE content.lg = '$lg'
              AND node.cache_box_depth = 0
              AND node.cache_active = 1
              AND content.active = 1
              AND node.cache_access_restricted_node_id = 0
              AND $parents_restriction
            GROUP BY content_id
            ORDER BY last_change DESC
        ");
        
        $feed_items = array();
        $last_update = null;
        foreach($last_updates as $update) {
            $content = new db_Content();
            $content->get($update['content_id']);
            $node = $content->get_node();
            $content->load_fields();
            $title = $content->title;
            $last_update = max($last_update, $update['last_change']);
            $updated = gmdate('Y-m-d\TH:i:s\Z', $update['last_change']);
            $feed_items []= compact('title', 'updated', 'node');
        }
        
        $node = db_Node::get_node('root');
        $root_content = $node->get_content($lg);
        $title = $root_content->title;
        $updated = gmdate('Y-m-d\TH:i:s\Z', $last_update);
        $feed = compact('title', 'updated', 'node');
        $smarty->assign('feed', $feed);
        
        $smarty->assign('feed_items', $feed_items);
    }
}
