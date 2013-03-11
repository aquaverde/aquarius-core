<?php 
/** List the most recent searches
  */
class action_lastsearches extends AdminAction implements DisplayAction {

    var $props = array('class', 'lg');
    
    /** FIXME: permits for site admin */
    function permit_user($user) {
      return $user->isSiteadmin();;
    }
    
    function process($aquarius, $request, $smarty, $result) {

        // How many entries to display
        $limit = 200;

        $search = DB_DataObject::factory('content_search');
        $search->lg = $this->lg;
        $search->limit($limit);
        $search->orderBy('time DESC');
        $search->find();

        $smarty->assign('search', $search);
        $result->use_template('lastsearches.tpl');
    }
}

