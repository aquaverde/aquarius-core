<?php 
/**
  * Action class for the Admin Interface: Menu, Export, etc. 
  * See also the file $DYNFORM_MODULE_ROOT/dynform.php
  */

class action_dynform_menu extends ModuleAction 
{
	var $modname = "dynform" ;
    var $props = array("class", "command", "lg") ;

    function valid($user) {
      if ($this->command == 'settings' && !$user->isSuperadmin()) return false;
      return (bool)$user;
    }
    
    function get_title() 
    {
    	switch($this->command)
    	{
    		case "data": return new Translation("dynform_data") ; 
    		default: return null ; 
    	}
    }
    
    function execute() 
    {
        global $aquarius;
        require_once "lib/db/Node.php";
        require_once('lib/libdynform.php') ;  
        global $DB;
        
        $DL = new Dynformlib ;
        
        $messages = array();
        $smarty = false;
        $action = false;
       
       	switch($this->command)
       	{
       		case "data":
       			$smarty = $aquarius->get_smarty_backend_container() ;
       			$smarty->tmplname = "module_dynform_data.tpl";
       			break ; 
       		
       		case "settings":
       			$smarty = $aquarius->get_smarty_backend_container() ;
       			$smarty->assign("options_fields", $DL->get_setting_value('option_fields')) ; 
       			$smarty->tmplname = "module_dynform_settings.tpl";
       			break ; 

			default: 
				throw new Exception("Unknown command: '$this->command'");
       	}
       	
       	return compact('messages', 'smarty', 'action');
	}
}


