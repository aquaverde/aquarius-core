<?php 

/** Extended search class for searches restricted to one subsite
  * This search requires the 'subsite' parameter to be set to the desired site node. Only content below that node will be found. */
class Subsite_Search extends Content_Search {

    function parameter_types() {
        $types = parent::parameter_types();
        $types['subsite'] = 'int string object';
        return $types;
    }

    function sanitize() {
        $valid = parent::sanitize();
        $this->subsite = db_Node::get_node($this->subsite);
        return $valid;
    }


    function restrictions() {
        $restrictions = parent::restrictions();

        $subsite = $this->subsite;
        if ($this->subsite) {
            $restrictions['subsite_only'] = "
                            node.cache_left_index > $subsite->cache_left_index
                        AND node.cache_right_index < $subsite->cache_right_index
            ";
        }

        return $restrictions;
    }
}
