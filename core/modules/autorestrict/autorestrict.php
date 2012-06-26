<?php
/** Automatically set the access_restriction flag on new nodes. 
  *
  * Sometimes people have the expectation that newly added nodes in some
  * areas are access_restricted. Commonly you would restrict the parent and be
  * done with it. However if you want to have differing access permissions on
  * the children this is not enough.
  * 
  * To activate, pass names or ID of nodes where children should get the
  * access_restriction flag set:
  * $config['autorestrict']['children of'] = array('lair', 316); 
  */
class Autorestrict extends Module {

    var $register_hooks = array('node_insert', 'node_move');
    
    var $short = "autorestrict";
    var $name  = "Automatically set the access_restriction flag on some new nodes.";

    function node_insert($node) { $this->check_restriction($node); }
    function node_move($node)   { $this->check_restriction($node); }
    
    function check_restriction($node) {
        foreach($this->conf('children of') as $nodestr) {
            $parent_node = db_Node::get_node($nodestr);
            
            if (!$parent_node) throw new Exception("Missing node '$nodestr' when checking to apply the access-restriction flag.");
            
            if ($node->parent_id == $parent_node->id) {
                $this->enable_restriction($node);
            }
        }
    }
    
    function enable_restriction($node) {
        Log::info("Autorestriction setting access_restricted on ".$node->idstr());
        $node->access_restricted = true;
        $node->update();
    }
}