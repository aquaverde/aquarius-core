<?php 
/** Extends file formtype to save the legend per file, not per content as is done by the file formtype.
  * WARNING: This implementation is not language sensitive!
  */
class Global_Legend_File extends Module {

    var $register_hooks = array('init_form', 'smarty_config_backend');

    var $short = "global_legend_file";
    var $name  = "File legends per file";

    function init_form($formtypes) {
        require_once 'lib/formtype.global_legend_file.php';
        $formtypes->add_formtype(new Formtype_global_legend_file('global_legend_file', 'file'));
    }
}
?>
