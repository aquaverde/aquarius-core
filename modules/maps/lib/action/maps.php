<?php
class action_maps extends ModuleAction {
	var $modname = "maps";
	var $props = array('class', 'op', 'lg');
}

class action_maps_showmap extends action_maps implements DisplayAction {
	
	function process($aquarius,$request,$smarty,$result) {
               
		$module 		= 							$this->get_module();
		$content_list 	= 							$module->get_content_list($this->lg);
			
		$smarty->assign("content_list_points",		$module->get_points($content_list));
		$smarty->assign("content_list_lines",		$module->get_lines($content_list));
		$smarty->assign("myXml",					$module->create_xml($content_list));    
		        
		$result->use_template("maps_edit.tpl");
	}
}
?>