<?php 
/**
  * Action class for the Admin Interface: Menu, Export, etc.
  */

class action_dynform_menu extends ModuleAction implements DisplayAction {
    var $modname = "dynform";
    var $props = array("class", "command", "lg");

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
    
    function process($aquarius, $request, $smarty, $result) {
        $DL = new Dynformlib ;
        
        
        switch($this->command) {
            case "data":
                $result->use_template("module_dynform_data.tpl");
                break ; 
            
            case "settings":
                $smarty->assign("options_fields", $DL->get_setting_value('option_fields')) ; 
                $result->use_template("module_dynform_settings.tpl");
                break ; 

            default: 
                throw new Exception("Unknown command: '$this->command'");
        }
	}
}


