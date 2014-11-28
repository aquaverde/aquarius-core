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

    $DL = new Dynformlib ;
    
    $possible_option_fields = $DL->get_setting_value('option_fields') ;
    $option_fields_enabled = trim($possible_option_fields) ; 


    $str .= '<select name="'.$name.'" size="1">' ;
    foreach (Dynformlib::$field_types as $type) {
        if ($name == "Option" && !$option_fields_enabled) continue;
        $type_name = $type['name'];
        $str .= '<option value="'.$type_name.'">'.str(new Translation($type_name)).'</option>';
    }
    $str .= '</select>';

    return $str;
}


