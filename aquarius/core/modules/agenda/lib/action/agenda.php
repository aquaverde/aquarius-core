<?php
class action_agenda extends ModuleAction {
	var $modname = "agenda";
	var $props = array('class', 'op', 'lg');
}

class action_agenda_showagenda extends action_agenda implements DisplayAction {
	
	function process($aquarius,$request,$smarty,$result) {
               
		$module = $this->get_module();
		$smarty->assign("my_data", $module->get_data());  
		        
		$result->use_template("agenda_overview.tpl");
	}
}

class action_agenda_editagenda extends action_agenda implements DisplayAction {
	
	function process($aquarius,$request,$smarty,$result) {
        $root = db_Node::get_node('my_agenda');
        $open_nodes = NodeTree::get_open_nodes('sitemap');
        array_unshift($open_nodes, $root->id); // Always open root node
        $tree = NodeTree::editable_tree($root, $this->lg, $open_nodes);
        NodeTree::add_controls($tree, $open_nodes, 'sitemap', true, $this->lg);
        $tree['show_toggle'] = false; // Hack: do not show toggle for root node

        $smarty->assign('entry', $tree);
        $smarty->assign('forallaction', Action::make('nodetree', 'forall'));

        // Hack: let the temaplate know that we want the sitemap controls
        $this->section = 'sitemap';

        $result->use_template("nodetree.tpl");
	}
}

class action_agenda_dayagenda extends action_agenda implements DisplayAction {
	
	function process($aquarius,$request,$smarty,$result) {
               
		$module = $this->get_module();
		if(!empty($request['datefield'])) {
			$smarty->assign("my_date", $request['datefield']);	
			$this->define_p_n_dates($request['datefield'],$smarty);							
			$smarty->assign("my_data", $module->get_data_by_date($request['datefield']));
		}
		elseif(!empty($request['go_previous'])) {
			$smarty->assign("my_date", $request['previous_date']);
			$this->define_p_n_dates($request['previous_date'],$smarty);
			$smarty->assign("my_data", $module->get_data_by_date($request['previous_date']));
		}
		elseif(!empty($request['go_next'])) {
			$smarty->assign("my_date", $request['next_date']);
			$this->define_p_n_dates($request['next_date'],$smarty);
			$smarty->assign("my_data", $module->get_data_by_date($request['next_date']));
		}
		        
		$result->use_template("agenda_day.tpl");
	}
	
	function define_p_n_dates($my_date,$smarty) {
		$datum = $my_date;
		list ($tag, $monat, $jahr) = explode (".", $datum);
		
		$tstamp_p = mktime(date("H"),date("i"),date("s"),date($monat),date($tag)-1,date($jahr));
		$tstamp_n = mktime(date("H"),date("i"),date("s"),date($monat),date($tag)+1,date($jahr));
				
		$smarty->assign("previous_date", date("d.m.Y",$tstamp_p));
		$smarty->assign("next_date", date("d.m.Y",$tstamp_n));
	}
}

?>