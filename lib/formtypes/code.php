<?php 
class Formtype_Code extends Formtype {
    /** Apply formtype specific conversion prior to editing content
     */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        $valobject->rows = max(3, $formfield->sup1); // Make it at least three lines
    }
}
?>