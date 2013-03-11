<?php 
class Nodesort {
    
    public $fieldname;
    public $form;
    private $reverse_numerical_sort;
    public function Nodesort($fieldname, $form) {
        $this->fieldname = $fieldname;
        $this->form = $form;
        $this->sort_reverse = $form->sort_reverse ? -1 : 1;
        $this->reverse_numerical_sort = (defined('FUGLIX_REVERTED_NUMERICAL_SORT') && FUGLIX_REVERTED_NUMERICAL_SORT)? -1 : 1;
    }
    
    function compare($entry1, $entry2) {
        $fieldname = $this->fieldname;
        $form = $this->form;
        $c1 = $entry1->get_content();
        $c2 = $entry2->get_content();
        $order = 0;
        if ($c1 && $c2) {
            $c1->load_fields();
            $c2->load_fields();
            $c1_set = isset($c1->$fieldname);
            $c2_set = isset($c2->$fieldname);
            if ($c1_set && $c2_set) {
                // If both values are numeric we sort numerically else we use alphabetical order
                if (is_numeric($c1->$fieldname) && is_numeric($c2->$fieldname)) {
                    if (function_exists('bccomp')) {
                        // Compare using arbitrary precision comparison function; scale=6 should cover the precision of your average float
                        $order = bccomp($c1->$fieldname, $c2->$fieldname, 6) * $this->reverse_numerical_sort;
                    } else {
                        $order = $c1->$fieldname - $c2->$fieldname;
                    }
                } else {
                    $order = strcasecmp($c1->$fieldname, $c2->$fieldname);
                }
            } else {
                // Empty fields go first
                $order = intval($c1_set) - intval($c2_set);
            }
        }
        return $order * $this->sort_reverse;
    }
}
?>