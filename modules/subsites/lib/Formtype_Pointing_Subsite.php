<?php 
/** Special version of the pointing field that restricts to current subsite
  *
  * sup1:
  *    Enable a drop-down for selection directly from contentedit. Not available for multi fields
  *
  * sup3:
  *    maximal depth of selection tree, specify 1 to give selection of children only
  *
  * sup4:
  *    comma-delimited list of depth where selection is prohibited (specify 0 to prohibit selection of the root node)
  */
class Formtype_Pointing_Subsite extends Formtype_Pointing {
    var $subsites; // Module dependency

    function __construct($code, $template, $subsites) {
        $this->subsites = $subsites;
        
        parent::__construct($code, $template);
    }

    function pre_contentedit($node, $content, $formtype, $formfield, $valobject, $page_requisites) {
        $subsite = $this->subsites->site_of_node($node);

        $frobulated_formfield = clone $formfield; // Maybe superfluous to clone it, but we don't know how it's used outside
        if ($subsite) $frobulated_formfield->sup2 = $subsite->id;

        // Let our parent class do the work
        return parent::pre_contentedit($node, $content, $formtype, $frobulated_formfield, $valobject);
    }
}
