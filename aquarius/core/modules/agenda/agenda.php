<?php

class Agenda extends Module {
	
	var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend');
    
    var $short = "agenda";
    var $name  = "Agenda Modul";

    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_modules',
            400,
            new Menu('agenda_menu', false, false, array(
            	1 => new Menu('agenda_edit', Action::make('agenda','editagenda', $lg)),
				2 => new Menu('agenda_overview', Action::make('agenda','showagenda', $lg)),
				3 => new Menu('agenda_day', Action::make('agenda','dayagenda', $lg))
            ))
       	);
    }

	function get_data() {
		$data = array();
		
		$node = db_Node::get_node('my_agenda');
		$children = $node->children();
		
		foreach ($children as $child_node) {
			$group = $child_node->title;
			$entries = $child_node->children();
			foreach ($entries as $entry) {
				$content = $entry->get_content();
				$content->load_field();
				$date = date("d.m.Y",$content->begin);
				
				$data[$group][$date][] = $content;				
			}
		}

		return $data;
	}
	
	function get_data_by_date($selected_date) {
		$data = array();
		
		$node = db_Node::get_node('my_agenda');
		$children = $node->children();

		foreach ($children as $child_node) {
			$group = $child_node->title;
			$entries = $child_node->children();
			foreach ($entries as $entry) {
				$content = $entry->get_content();
				$content->load_field();
				$date = date("d.m.Y",$content->begin);
				
				if($date == $selected_date) {	
					$data[$content->time][$group][] = $content;
				}
					
			}
		}
		array_multisort($data);

		return $data;
	}
}

?>