<?
/* TODO: eventually it would be good to have a spinner with the groups? */
class action_fegroup extends AdminAction {
    
    var $props = array("class", "command", "id", "spinner");
    
    /** FIXME: Always permits for logged-in users */
    function permit_user($user) {
      return $user->isSiteadmin();
    }
    
    function execute() {
	
		$smarty = false;
		$messages = array();
        
		require_once "lib/spinner.class.php";
		
		$fe_groups = DB_DataObject::factory('fe_groups');
		
		switch ($this->command)
		{
			case 'list':

                global $aquarius;
                $smarty = $aquarius->get_smarty_backend_container();
				$fe_groups->find();
		
				$groups = array();
					
				while ( $fe_groups->fetch() )
					$groups[] = clone($fe_groups);
					
				
				$smarty->assign("groups", $groups);
				$smarty->tmplname = 'fe_group_list.tpl';
		
				break;
			case 'edit':
				require_once "lib/db/Node.php";
				
                global $aquarius;
                $smarty = $aquarius->get_smarty_backend_container();
				
				$fe_groups->id = $this->id;
				$fe_groups->find();
				$fe_groups->fetch();
				
				// load the node tree
				$rootnode = db_Node::get_root();
                $purge_filter = NodeFilter::create('access_restricted', true);
				$nodelist = NodeTree::build_flat($rootnode, array(), false, false, $purge_filter);
					
				// get the stored restriction for this group
				$restr_proto = DB_DataObject::factory('fe_restrictions');
				$restr_proto->group_id = $this->id;
				$restr_proto->find();
				
				$restrictions = array();
				
				while ( $restr_proto->fetch() )
					$restrictions[$restr_proto->node_id] = true;
				
				$smarty->assign("restrictions", $restrictions);
				$smarty->assign("nodelist", $nodelist);
				$smarty->assign("group", $fe_groups);
				$smarty->tmplname = 'fe_group_edit.tpl';
				break;
			case 'save':
				// set the new group name
				$fe_groups->name = $_POST['groupName'];
				
				// save the group
				if ( $this->id != "null" ) {
					$fe_groups->id = $this->id;
					$fe_groups->update();
				} else {
					$fe_groups->insert();
				}
				
				// delete old restricons
				$restriction = DB_DataObject::factory('fe_restrictions');
				$restriction->group_id = $fe_groups->id;
				$restriction->delete();

				if ( !empty($_POST['nodeId']) ) {
					// set the new restrictions and save them
					foreach ( array_keys($_POST['nodeId']) as $key ) {
						$restriction->node_id = $key;
						$restriction->insert();
					}
				}
                break;            
            case 'delete':
                $fe_groups->get($this->id);
                $fe_groups->delete(); 
				$messages[] = "Group deleted";
				break;
			case "toggle_active":
       			$fe_groups->get($this->id);
				$fe_groups->active = !$fe_groups->active;
				$fe_groups->update();
				$messages[] = "Group switched";
				break;
  			default:
				throw new Exception("Operation unknown: '$this->command'");
		}
		
		return array('messages'=>$messages, 'smarty'=>$smarty);
	}

}
?>