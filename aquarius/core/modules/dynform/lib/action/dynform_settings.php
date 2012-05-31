<?
/**
  * Action class for the Admin Interface: Dynform data export
  * See also the file $DYNFORM_MODULE_ROOT/dynform.php
  */
  
class action_dynform_settings extends ModuleAction 
{
	var $modname = "dynform" ;
    var $props = array("class", "command", "form_id") ;

    function valid($user) {
      return (bool)$user;
    }
    
    function execute() 
    {
        global $aquarius;
        require_once("lib/db/Node.php") ;
        require_once('lib/libdynform.php') ;  
        global $DB;
        
        $DL = new Dynformlib ;
        
        $messages = array();
        $smarty = false;
        $action = false;
        
        switch($this->command)
       	{
       		case 'update_option_fields':
       			$option_fields = $_POST['option_fields'] ;  
       			$dsetting = new db_Dynform_settings ;
				$dsetting->keyword = 'option_fields' ; 
				$res = $dsetting->find() ; 
				if ($res) {
					$dsetting->fetch() ;
					$dsetting->value = $option_fields ; 
					$dsetting->update() ; 
				}
				else {
					$dsetting->value = $option_fields ; 
					$dsetting->insert() ; 
				}
       			break ; 
       			
       		default: 
				throw new Exception("Unknown command: '$this->command'") ;
				break ; 
       	}
       
       	return compact('messages', 'smarty', 'action');
	}
}


