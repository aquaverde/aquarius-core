<?php 
/** Show the most recent changes to content */
class action_lastchanges extends AdminAction implements DisplayAction {
    var $props = array('class');
    
    /** Permit for logged-in users */
    function permit_user($user) {
      return (bool)$user;
    }
    
    function process($aquarius, $request, $smarty, $result) {
        global $DB;
        
        // How many entries to display
        $limit = LASTCHANGES_COUNT;
        // Maybe we received the limit as parmeter
        if (@is_numeric($this->params[0])) $limit = max(min(intval($this->params[0]), 1), 1000); // Limit to sane values

        $entries = $DB->queryhash("
            SELECT content_id, user_id, MAX(last_change) as last_change
            FROM journal
            GROUP BY content_id, user_id
            ORDER BY last_change DESC
            LIMIT ".$limit
        );

        $journal_info = array();
        foreach($entries as $entry) {
            $user = DB_DataObject::factory('users');
            $found_user = $user->get($entry['user_id']);
            if ($found_user) $entry['user'] = $user;
            $content = DB_DataObject::factory('content');
            $found_content = $content->get($entry['content_id']);
            if ($found_content) {
                $entry['content'] = $content;
                $journal_info[] = $entry;
            }
        }
        $smarty->assign('journal_info', $journal_info);
        $result->use_template("lastchanges.tpl");
    }
}

