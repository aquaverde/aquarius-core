<?php 
/** 
  * autmated dynform rendering, hc, does everything
  * eats form_object (when a content object has a pointing to a dynform, it's a form object)
  */

  
function smarty_function_render_form_entry($params, &$smarty) {
	require_once "lib/db/Wording.php";
	$DL = new Dynformlib ; 
    $lg = get($params, 'lg', null) ; 
    $entry_id = get($params, 'entry_id', null) ; 
    if (!$entry_id) { 
    	$smarty->trigger_error("render_form_entry: require parameter <b>entry_id</b> missing") ;
    	return ; 
    }
    if (!$lg) { 
    	$smarty->trigger_error("render_form_entry: require parameter <b>lg</b> missing") ;
    	return ; 
    }
    
    $fallback_lg = get($params, 'fallback_lg', "null") ; 
    
    $entry = new db_Dynform_entry ;
    $entry->id = $entry_id ; 
    if (!$entry->find()) { echo "Could not find entry with id: $entry_id." ; return ; }
    $entry->fetch() ; 
   
   
    $str = "" ; 
    $str .= "<br /><br />". str(new Translation("entry_of")).": ".$DL->format_sql_time($entry->time) ; 
    
    $str .= '<form method="post" name="mailForm" action="'.str($smarty->get_template_vars('url')).'" id="form" onsubmit="return checkFormSubmit(this);">' ; 
    $str .= '<div class="form-block">' ; 
	$str .= '' ; 
   
	$dynform = new db_Dynform ; 
	$dynform->id = $entry->dynform_id ;
	$found = $dynform->find() ; 
	
	if ($found) 
	{
		$dynform->fetch() ;
		$blocks = array() ;
		$dblock = new db_Dynform_block ;
		$dblock->dynform_id = $dynform->id ; 
		$dblock->orderBy('weight ASC') ;
		$dblock->find() ; 
		while ($dblock->fetch()) {
			$blocks[] = clone($dblock) ; 
		}
		
		$str .= '
		<table class="table">
			<colgroup>
				<col class="talon-left" />
				<col class="talon-right" />
			</colgroup>' ; 
		
		foreach ($blocks as $block)
		{
			$str .= '<tr><td colspan="2" class="blockTitle">'.$DL->get_block_name($block->id, $lg).'</td></tr>' ; 
			$fields = array() ; 
			$dfield = new db_Dynform_field ; 
			$dfield->block_id = $block->id ; 
			$dfield->orderBy('weight ASC') ;
			$dfield->find() ; 
			while ($dfield->fetch()) {
				$fields[] = clone($dfield) ; 
			}
			foreach ($fields as $field)
			{
				$ftype = $DL->get_fieldtype_name($field->type) ; 
				
				if ($ftype == "Text")
				{
					 $str .= '<tr><td colspan="2" class="formLabel">'.$DL->get_field_options($field->id, $lg).'</td></tr>' ; 
				}
				else 
				{
					$str .= '<tr><td><label>'.$DL->get_field_name($field->id, $lg) ; 
					//if ($field->required) $str .= ' *' ; 
					$str .= '</label></td>' ;

					switch ($ftype)
					{
						case "Singleline":
						case "Number":
						case "Email":
						case "Checkbox":
						case "Radiobutton":
						case "Pulldown":
							$str .= '
							<td class="formInput">
								<input type="text" name="field_'.$field->id.'" id="field_'.$field->id.'" class="form-control' ; 
								$str .='" value="'.$DL->get_entry_value_for_field($entry->id, $field->id).'" ' ; 
								if ($field->width) {
									$str .= 'style="width:'.($field->width * 10).'px;" maxlength="'.$field->width.'"' ; 
								}
								$str .=' />
							</td>' ; 
							break ; 

						case "Multiline":
							$str .= '
							<td class="formInput">
								<textarea type="text" class="form-control" name="field_'.$field->id.'" id="field_'.$field->id.'"' ; 
								$str .= '" ' ; 
								if ($field->num_lines) {
									$str .= 'style="height:'.($field->num_lines * 13).'px;" ' ; 
								}
								$str .= '>'.$DL->get_entry_value_for_field($entry->id, $field->id).'</textarea>
							</td>' ; 
							break ; 


						case "Upload":


							$str .= '
							<td class="formInput">' ;
								
								if ( is_image( $DL->get_entry_value_for_field($entry->id, $field->id) ) ) {
									$str .= '<a href="'.$DL->get_entry_value_for_field($entry->id, $field->id).'" target="_blank"><img src="'.$DL->get_entry_value_for_field($entry->id, $field->id).'" width="200"></a>' ;
								} else {
									$str .= '<a href="'.$DL->get_entry_value_for_field($entry->id, $field->id).'" target="_blank">'.$DL->get_entry_value_for_field($entry->id, $field->id).'</a>' ;
								}

							$str .=' </td>' ; 
							break ; 

						default:
							$str .= '<td class="formInput">'.$ftype.'</td>' ; 
							break ; 
					}	
					$str .= '</tr>' ; 
				}
			}	
		}
	}
	


	
	require_once("lib/action.php") ;
	$cancelaction = Action::make("dynform_data", "show", $entry->dynform_id, $fallback_lg) ;
	$saveaction = Action::make("dynform_data", "edit_entry_submit", $entry_id, $fallback_lg) ; 
		
	$str .= '			
		</table>
        <input type="submit" name="'.str($saveaction).'" value="'.str(new Translation('s_save')).'" class="btn btn-primary"/>
		<input type="submit" name="" value="'.str(new Translation('s_cancel')).'" class="btn btn-default"/>' ; 
		
	$str .= '</div>' ; 
	$str .= '</form>' ; 
	
	echo $str ; 
}

function is_image($path)
{
    $a = getimagesize($path);
    $image_type = $a[2];
     
    if(in_array($image_type , array(IMAGETYPE_JPEG ,IMAGETYPE_PNG)))
    {
        return true;
    }
    return false;
}
