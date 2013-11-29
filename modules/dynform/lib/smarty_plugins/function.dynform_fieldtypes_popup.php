<?php

/** 
  * This constucts a pop up menu with all the possibel dynform field types
  * An optional "name" can be specified
  * Assigns nothing
  */
  
function smarty_function_dynform_fieldtypes_popup($params, &$smarty) 
{    
    $name = get($params, 'name', 'new_fieldtype') ;
    $str = "" ; 
    
    require_once('lib/libdynform.php') ;  
    $DL = new Dynformlib ;
    
    $possible_option_fields = $DL->get_setting_value('option_fields') ;
    $option_fields_enabled = trim($possible_option_fields) ; 
    
    $ft = DB_DataObject::factory('dynform_field_type') ; 
    $found = $ft->find() ; 
    if ($found) 
    {
		$str .= '<select name="'.$name.'" size="1">' ;
		$types = array() ; 
		while ($ft->fetch()) {
			$types[] = clone($ft) ; 
		}
		foreach ($types as $type) {
			if ($type->name == "Option" && !$option_fields_enabled) continue ; 
			$str .= '<option value="'.$type->name.'">'.str(new Translation($type->name)).'</option>' ; 
		} 
		$str .= '</select>' ; 
	}
	return $str ;     
}


?>