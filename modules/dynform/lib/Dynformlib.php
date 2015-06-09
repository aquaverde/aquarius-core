<?php

require_once("pear/Date.php") ; 

class Dynformlib {
    
    static $field_types = array(
        1 => array('name' => 'Singleline', 'template' => 'df_singleline'),
        2 => array('name' => 'Multiline', 'template' => 'df_multiline'),
        3 => array('name' => 'Checkbox', 'template' => 'df_checkbox'),
        4 => array('name' => 'Pulldown', 'template' => 'df_pulldown'),
        5 => array('name' => 'Radiobutton', 'template' => 'df_radiobutton'),
        6 => array('name' => 'Text', 'template' => 'df_text'),
        7 => array('name' => 'Email', 'template' => 'df_email'),
        8 => array('name' => 'Number', 'template' => 'df_number'),
        9 => array('name' => 'Option', 'template' => 'df_options_from_form'),
        10 => array('name' => 'TargetEmail', 'template' => 'df_target_email'),
        11 => array('name' => 'Nodelist', 'template' => 'df_nodelist'),
        12 => array('name' => 'Upload', 'template' => 'df_upload'),
        13 => array('name' => 'Nodelist', 'template' => 'df_nodelist')
    );

	// options from form stuff

	function get_setting_value($key)
	{
		$dsetting = new db_Dynform_settings ;
		$dsetting->keyword = $key ; 
		$res = $dsetting->find() ; 
		if ($res) {
			$dsetting->fetch() ; 
			return $dsetting->value ; 
		}
		return "" ; 
	}

    function get_options_from_form_fields() {
        $vals = array_map('trim', explode(';', $this->get_setting_value('option_fields')));
        $pairs = array() ; 
        foreach ($vals as $val) {
            if (strlen($val) > 0) {
                $entry = array() ;
                $kvp = array_map('trim', explode(',', $val));
                if (count($kvp) > 1) {
                    $entry['key'] = $kvp[0] ;
                    $entry['value'] = $kvp[1] ;
                    $pairs[] = $entry ;
                }
            }
		}
		return $pairs ; 
	}
	
	

	// this function aligns all block weights for one dynform, so the steps are always based on 10. 
	// required for the move_block function to work. only called after a block delete has occured
	
	function update_block_weights($dynform_id)
	{
		$dblock = new db_Dynform_block ;
		$dblock->dynform_id = $dynform_id ; 
		$dblock->orderBy('weight ASC') ;
		$dblock->find() ; 
		$cur_weight = 0 ; 
		while ($dblock->fetch()) {
			$cur_weight += 10 ; 
			$dblock->weight = $cur_weight ; 
			$dblock->update() ;  
		}
	}
	
	function move_block($block_id, $direction) 
	{
		if ($direction == "DOWN") $up = false ; 
		else if ($direction == "UP") $up = true ; 
		else return ; 
		$block = new db_Dynform_block ; 
		$block->id = $block_id ; 
		$found = $block->find() ;
		if ($found) 
		{
			$block->fetch() ; 
			$weight = $block->weight ; 
			
			// find the lowest block for this dynform.
			$dblock = new db_Dynform_block ;
			$dblock->query("SELECT weight FROM {$dblock->__table} WHERE dynform_id=".$block->dynform_id." ORDER BY weight ". ($up ? "ASC" : "DESC") ." LIMIT 1") ;
			$dblock->fetch() ; 
			$lowest = $dblock->weight ;
			if ($weight == $lowest) return ;
			
			// not the lowest weightet block: find next block and swap weight
			$prevblock = new db_Dynform_block ; 
			$prevblock->dynform_id = $block->dynform_id ; 
			
			$prevblock->weight = $up ? $weight - 10 : $weight + 10 ; 
			$found = $prevblock->find() ; 
			if ($found)
			{
				$prevblock->fetch() ; 
				$prevblock->weight = $weight ; 
				$prevblock->update() ; 
				$block->weight = $up ? $weight - 10 : $weight + 10 ; 
				$block->update() ; 
			}
		}
	}
	
	function delete_block($block_id)
	{
		$block = new db_Dynform_block ; 
		$block->id = $block_id ; 
		$found = $block->find() ;
		if ($found) 
		{
			$dfield = new db_Dynform_field ;
			$dfield->block_id = $block_id ; 
			$found = $dfield->find() ; 
			if ($found)
			{
				while ($dfield->fetch()) {
					$this->delete_field($dfield->id) ;  
				}
			}
			$block->delete() ; 
			$this->update_block_weights($block->dynform_id) ;  
		}
	}
	
	function update_field_weights($block_id)
	{
		$dfield = new db_Dynform_field ;
		$dfield->block_id = $block_id ; 
		$dfield->orderBy('weight ASC') ;
		$found = $dfield->find() ; 
		if ($found)
		{
			$cur_weight = 0 ; 
			while ($dfield->fetch()) {
				$cur_weight += 10 ; 
				$dfield->weight = $cur_weight ; 
				$dfield->update() ;  
			}
		}
	}
	
	function delete_field($field_id)
	{
		$field = new db_Dynform_field ; 
		$field->id = $field_id ; 
		$found = $field->find() ;
		if ($found) 
		{
			$fdata = new db_Dynform_field_data ; 
			$fdata->field_id = $field->id ; 
			$found = $fdata->find() ; 
			if ($found) {
				while ($fdata->fetch()) $fdata->delete() ; 
			}
			$field->fetch() ; 
			$block_id = $field->block_id ; 
			$field->delete() ; 
			$this->update_field_weights($block_id) ;  
		}
	}
	
	function move_field($field_id, $direction) 
	{
		if ($direction == "DOWN") $up = false ; 
		else if ($direction == "UP") $up = true ; 
		else return ; 
		$field = new db_Dynform_field ; 
		$field->id = $field_id ; 
		$found = $field->find() ;
		if ($found) 
		{ 
			$field->fetch() ; 
			$weight = $field->weight ; 
			
			// find the lowest field for this block.
			$dfield = new db_Dynform_field ;
			$dfield->query("SELECT weight FROM {$dfield->__table} WHERE block_id=".$field->block_id." ORDER BY weight ". ($up ? "ASC" : "DESC") ." LIMIT 1") ;
			$dfield->fetch() ; 
			$lowest = $dfield->weight ;
			if ($weight == $lowest) return ;
			
			// not the lowest weightet block: find next block and swap weight
			$prevfield = new db_Dynform_field ; 
			$prevfield->block_id = $field->block_id ; 
			
			$prevfield->weight = $up ? $weight - 10 : $weight + 10 ; 
			$found = $prevfield->find() ; 
			if ($found)
			{
				$prevfield->fetch() ; 
				$prevfield->weight = $weight ; 
				$prevfield->update() ; 
				$field->weight = $up ? $weight - 10 : $weight + 10 ; 
				$field->update() ; 
			}
		}
	}
	
	static function get_fieldtype_template($name) {
        foreach(self::$field_types as $ftype) {
            if ($ftype['name'] == $name) return $ftype['template'].'.tpl';
        }
		throw new Exception("Undefined dynform field $name");
	}
	
	static function get_fieldtype_id($name) {
        foreach(self::$field_types as $id => $ftype) {
            if ($ftype['name'] == $name) return $id;
        }
        throw new Exception("Undefined dynform field $name");
	}
	
	static function get_fieldtype_name($fid) {
        if (isset(self::$field_types[$fid])) return self::$field_types[$fid]['name'];
        throw new Exception("Undefined dynform field id $fid");
    }
	
	/** called from lib/action/node.php on every node copy */
	
	function copy_dynform($source_node, $target_node)
	{
		if ($this->is_dynform_node($source_node)) 
		{
			$dynform = new db_Dynform ; 
			$dynform->node_id = $source_node->id ; 
			$found = $dynform->find() ; 
			if ($found)
			{
				$dynform->fetch() ; 
				$new_df = new db_Dynform ; 
				$new_df->node_id = $target_node->id ; 
				$new_df->insert() ; 
				
				$block = new db_Dynform_block ; 
				$block->dynform_id = $dynform->id ; 
				$found = $block->find() ; 
				if ($found) {
					while ($block->fetch()) {
						$this->copy_block($block, $new_df->id) ; 
					}
				}
			}
		}
	}
	
	/** called from lib/action/node.php on every node delete */
	
	function delete_dynform($node)
	{
		if ($this->is_dynform_node($node)) 
		{
			$dynform = new db_Dynform ; 
			$dynform->node_id = $node->id ; 
			$found = $dynform->find() ; 
			if ($found)
			{
				$dynform->fetch() ; 
				$block = new db_Dynform_block ; 
				$block->dynform_id = $dynform->id ; 
				$found = $block->find() ; 
				if ($found) {
					while ($block->fetch()) {
						$this->delete_block($block->id) ; 
					}
				}
			}
		}
	}
	
	function is_dynform_node($node)
	{
		$nodeform = new db_Form ; 
		$nodeform->id = $node->form_id ; 
		$found = $nodeform->find() ; 
		if ($found) {
			$nodeform->fetch() ; 
			if (FALSE != stristr($nodeform->title, "Dynform_node")) {
				return true ; 
			}
		}
		return false ; 
	}
	
	function copy_block($block, $target_df_id)
	{
		$newblock = new db_Dynform_block ; 
		$newblock->dynform_id = $target_df_id ; 
		$newblock->name = $block->name ; 
		$newblock->weight = $block->weight ; 
		$newblock->insert() ; 
		
		$block_data = new db_Dynform_block_data ; 
		$block_data->block_id = $block->id ; 
		$found = $block_data->find() ; 
		if ($found)
		{
			while ($block_data->fetch())
			{
				$new_data = new db_Dynform_block_data ; 
				$new_data->block_id = $newblock->id ; 
				$new_data->lg = $block_data->lg ; 
				$new_data->name = $block_data->name ; 
				$new_data->insert() ; 
			}
		}
		
		$field = new db_Dynform_field ; 
		$field->block_id = $block->id ; 
		$found = $field->find() ; 
		if ($found)
		{
			while ($field->fetch())
			{
				$newfield = new db_Dynform_field ; 
				$newfield->block_id = $newblock->id ; 
				$newfield->type = $field->type ; 
				$newfield->name = $field->name ; 
				$newfield->weight = $field->weight ; 
				$newfield->required = $field->required ; 
				$newfield->num_lines = $field->num_lines ; 
				$newfield->width = $field->width ; 
				$newfield->insert() ; 
				
				$fdata = new db_Dynform_field_data ; 
				$fdata->field_id = $field->id ; 
				$found = $fdata->find() ; 
				if ($found)
				{
					$fdata->fetch() ; 
					$newdata = new db_Dynform_field_data ; 
					$newdata->field_id = $newfield->id ; 
					$newdata->lg = $fdata->lg ; 
					$newdata->name = $fdata->name ; 
					$newdata->options = $fdata->options ; 
					$newdata->insert() ; 
				}
			}
		}
	}
	
	function get_block_name($bid, $lg)
	{
		$bdata = DB_DataObject::factory('dynform_block_data') ; 
		$bdata->block_id = $bid ; 
		$bdata->lg = $lg ; 
		$found = $bdata->find() ; 
		if ($found)
		{
			$bdata->fetch() ; 
			return $bdata->name ; 
		}
		else
		{
			$bl = DB_DataObject::factory('dynform_block') ; 
			$bl->id = $bid ; 
			if ($bl->find()) 
			{
				$bl->fetch() ; 
				return $bl->name ;
			}
			else return "" ; 
		}
	}
	
	function get_form_name($fid, $lg=false) 
	{
		if ($lg == "null" || $lg == false) { $lg = $this->get_default_lg() ; } 
		
		$dynform = new db_Dynform ; 
		$dynform->id = $fid ; 
		if ($dynform->find())
		{
			$dynform->fetch() ; 
			$node_id = $dynform->node_id ; 
			$content = new db_Content ; 
			$content->node_id = $node_id ; 
			$content->lg = $lg ; 
			if ($content->find())
			{
				$content->fetch() ; 
				$content_field = new db_Content_field ; 
				$content_field->content_id = $content->id ; 
				$content_field->name = "title" ; 
				if ($content_field->find())
				{
					$content_field->fetch() ; 
					$value = new db_Content_field_value ; 
					$value->content_field_id = $content_field->id ; 
					if ($value->find())
					{
						$value->fetch() ; 
						return $value->value ; 
					}
				}
			}
		}
		else return "" ; 
	}
	
	function get_field_name($fid, $lg, $length=1024)
	{
        return strip_tags($this->get_field_name_nostriptags($fid, $lg, $length)) ;
	}

	function get_field_name_nostriptags($fid, $lg, $length=1024)
	{
		$fdata = DB_DataObject::factory('dynform_field_data') ; 
		$fdata->field_id = $fid ; 
		$fdata->lg = $lg ; 
		$found = $fdata->find() ; 
		if ($found)
		{
			$fdata->fetch() ; 
			return $this->truncate_str($fdata->name, $length) ; 
		}
		else
		{
			$fl = DB_DataObject::factory('dynform_field') ; 
			$fl->id = $fid ; 
			if ($fl->find()) 
			{
				$fl->fetch() ; 
				return $this->truncate_str($fl->name, $length) ;
			}
			else return "" ; 
		}
	}
	
	function truncate_str($string, $length=1024) 
	{
		if (strlen($string) < $length) return $string ; 
		else {
			$str = substr($string, 0, $length) ;
			return $str."..." ; 
		}
	}
	
	function get_field_options($fid, $lg) 
	{
		$fdata = DB_DataObject::factory('dynform_field_data') ; 
		$fdata->field_id = $fid ; 
		$fdata->lg = $lg ; 
		$found = $fdata->find() ; 
		if ($found)
		{
			$fdata->fetch() ; 
			return $fdata->options; 
		}
		else return "" ;
	}
	
	function get_entry_value_for_field($entry_id, $field_id)
	{
		if (!$entry_id || !$field_id) return "" ; 
		$entrydata = new db_Dynform_entry_data ; 
		$entrydata->field_id = $field_id ; 
		$entrydata->entry_id = $entry_id ; 
		if ($entrydata->find())
		{
			$entrydata->fetch() ; 
			return $entrydata->value ; 
		}
		return "" ; 
	}
	
	function get_data_fields_ids($fid) 
	{
		$blocks = array() ;
		$fields = array() ;
		$dblock = new db_Dynform_block ;
		$dblock->dynform_id = $fid ; 
		$dblock->orderBy('weight ASC') ;
		$dblock->find() ; 
		while ($dblock->fetch()) {
			$blocks[] = clone($dblock) ; 
		}
		foreach ($blocks as $block)
		{
			$dfield = DB_DataObject::factory('dynform_field') ; 
			$dfield->block_id = $block->id ; 
			$dfield->orderBy('weight ASC') ;
			$dfield->find() ; 
			while ($dfield->fetch()) {
				if ($this->get_fieldtype_name($dfield->type) == "Text") continue ; 			// dont add text only fields
				$fields[] = $dfield->id ; 
			}
		}
		return $fields ; 
	}
	
    
    function get_default_lg() {
        global $DB ; 
        $query = "SELECT lg FROM languages ORDER BY weight DESC LIMIT 1" ; 
        return $DB->singlequery($query);
    }
	
	function get_column_titles($fid, $lg=false)
	{
		if (!$lg) $lg = $this->get_default_lg() ; 
		$columntitles = array() ; 
		$columntitles['id'] = "ID" ;
		$columntitles['date'] = new Translation("date") ; 
		$columntitles['lg'] = new Translation("language") ; 
		$columntitles['submitnodetitle'] = new Translation("submit_node") ;
		$fields_ids = $this->get_data_fields_ids($fid) ; 
		foreach ($fields_ids as $field_id) {
			$columntitles[$field_id] = $this->get_field_name($field_id, $lg) ; 
		}
		return $columntitles ; 
	}

	function get_entries_data($form_id, $lg=false) { 
		$field_ids = $this->get_data_fields_ids($form_id) ;
		
		global $DB;
        $form_id = intval($form_id);
		$result = $DB->queryhash("
            SELECT entry_id, field_id, lg, time, submitnodetitle, value
            FROM dynform_entry
            JOIN dynform_entry_data ON entry_id = dynform_entry.id
            WHERE dynform_id = $form_id ".
		    ($lg ? "AND lg = '$lg'":"")."
		      AND field_id IN (".join(',', $field_ids).")
		    ORDER BY time DESC;
		");

		$data = array();
		foreach($result as $field) {
            $entry_id = $field['entry_id'];
            if (!isset($data[$entry_id])) {
                $data[$entry_id] = array(
                    'id'              => $entry_id,
                    'date'            => $this->format_sql_time($field['time']),
                    'lg'              => $field['lg'],
                    'submitnodetitle' => $field['submitnodetitle']
                );
            }
            $data[$entry_id][$field['field_id']] = $field['value'];
		}
		return $data;
	}
	
	function format_sql_time($time)
	{
		if (!$time) return "" ; 
		$year = substr($time, 0, 4) ; 
		$month = substr($time, 5, 2) ; 
		$day = substr($time, 8, 2) ; 
		$clock = substr($time, 11, 5) ; 
		return $day.'.'.$month.'.'.$year.' '.$clock ; 
	}
	
	function update_entry($entry_id, $post_vars)
	{
		$entry = new db_Dynform_entry ; 
		$entry->id = $entry_id ; 
		if (!$entry->find()) { echo "Error trying to update an non existing entry , $entry_id" ; return ; } 
		$entry->fetch() ;
		$dynform_id = $entry->dynform_id ;
	
		$dynform = new db_Dynform ; 
		$dynform->id = $dynform_id ;
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
			foreach ($blocks as $block)
			{
				$fields = array() ; 
				$dfield = new db_Dynform_field ; 
				$dfield->block_id = $block->id ; 
				$dfield->orderBy('weight ASC') ;
				$dfield->find() ; 
				while ($dfield->fetch()) {
					$fields[] = clone($dfield) ; 
				}
				foreach ($fields as $field) {
					$ftype = $this->get_fieldtype_name($field->type) ; 
					if ($ftype == "Text") continue ;   // no entries for texts
					$value = $post_vars['field_'.$field->id] ; 
					$entry_data = new db_Dynform_entry_data ; 
					$entry_data->entry_id = $entry_id ; 
					$entry_data->field_id = $field->id ; 
					if ($entry_data->find()) {
						$entry_data->fetch() ; 
						$entry_data->value = stripslashes($value) ; 
						$entry_data->update() ; 
					}
					else {
						$entry_data->value = stripslashes($value) ;
						$entry_data->insert() ; 
					}
				}
			}
		}
	}
}

