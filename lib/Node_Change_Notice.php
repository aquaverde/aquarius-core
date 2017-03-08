<?php
/** Communicates changes to a node or changes to its children.
  */
class Node_Change_Notice {
    /* The Changed node */
    var $changed_node;
    
    /* Whether the change was structural (additions / deletions) */
    var $structural;
    
    /* Whether the change affects children (activation) */
    var $affects_children;
    
    /** Create instance of a change notice.
      * $param $changed_node     The node that was changed.
      * $param $structural       Flag whether the structure of the node tree was changed.
      * $param $affects_children Flag whether children are affected by this change, this is always true when $structural is true.
      */
    function __construct($changed_node, $structural, $affects_children) {
        $this->changed_node = $changed_node;
        $this->structural = $structural;
        $this->affects_children = $structural || $affects_children;
    }
    
    static function structural_change_to($node) {
        return new self($node, true, true);
    }
    
    static function affecting_children_of($node) {
        return new self($node, false, true);
    }
    
    static function concerning($node) {
        return new self($node, false, false);
    }
    
    /* Create a new notice by merging two notices.
     * If one of the notices has $affects_children or $structural set, it will be set in the new notice too. If the changed node is not the same, the common parent of both change notices will be used in the new notice and the flag 'affects_children' will be set. 
     *
     * @param $other the other notice to merge with this
     *
     * @return new notice covering both notices
     */
    function merge(Node_Change_Notice $other) {
        $structural = $this->structural || $other->structural;
        $affects_children =  $this->affects_children || $other->affects_children;

        $common_parent = $this->changed_node;
        while(!($common_parent->id == $other->changed_node->id 
             || $common_parent->ancestor_of($other->changed_node)
        )) {
            $common_parent = $common_parent->get_parent();
            if (!$common_parent) throw new Exception("Unable to find common parent of $this and $other");
            $affects_children = true;
        }

        return new self
            ( $common_parent
            , $structural
            , $affects_children
            );
    }
}
