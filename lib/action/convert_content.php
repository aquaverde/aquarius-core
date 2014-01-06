<?php 

/** Convert DB
  $ This action loads all content from the DB and saves it again. Handy in the case of DB format changes but not commonly used.
*/

class action_convert_content extends AdminAction {

    var $props = array('class', 'command', 'spec');
        
    /** allows superadmins */
    function permit_user($user) {
        return $user->isSuperadmin();
    }
    
        
    function execute() {
        global $DB;
        $messages = array();
        $smarty = false;
        $action = false;
        $node = DB_DataObject::factory('node');
        $node->find();
        while($node->fetch()) {
            $contents = $node->get_all_content();
            foreach($contents as $content) {
                $content->load_fields();
                $content->save_content();
            }
        }
        
        return compact('messages', 'smarty', 'action');
    }
}
