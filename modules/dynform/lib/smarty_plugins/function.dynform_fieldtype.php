<?php

/** 
  * Convenience method to transform a field type id to the current admin language
  * Requires "id"
  * Assigns nothing
  */
 
function smarty_function_dynform_fieldtype($params, $smarty) {
    $id = get($params, 'id') ;
    if (!$id) $smarty->trigger_error("dynform_fieldtype: require parameter id missing") ;

    return Dynformlib::get_fieldtype_name($id);
}
