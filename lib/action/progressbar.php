<?php
class action_progressbar extends AdminAction {
    
    var $props = array("class", "title");

    /** FIXME: Always permits for logged-in users */
    function permit_user($user) {
      return (bool)$user;
    }
	
    /** Returns smarty container for progress.tpl */
    function execute() {
		$smarty = false;
		$messages = array();
		
		$smarty = get_smart();
		
		$smarty->assign("title", $this->title);
		$smarty->tmplname = 'progress.tpl';
		
		return array('messages'=>$messages, 'smarty'=>$smarty);
	}

}
