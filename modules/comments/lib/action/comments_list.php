<?php

/** 
  */
class action_comments_list extends ModuleAction implements DisplayAction {
    var $modname = "comments";
    var $props = array("class");
    var $named_props = array('beforedate', 'afterdate', 'beforecomment', 'aftercomment', 'words', 'nodes', 'show_rejected', 'offset');

    function valid($user) {
      return (bool)$user;
    }
    
    function get_title() {
        return new Translation('comments_list');
    }
    
    function process($aquarius, $request, $smarty, $result) {
        global $DB;
        $comment_limit = 20;
        
        $search_new = new Comments_Search($DB);
        $search_new->limit = $comment_limit;
        $search_new->status = 'new';
        $new_comments = $search_new->find();
        $smarty->assign('new_comments', $new_comments->comments);
        $smarty->assign('new_actions', array(
            Action::make('comments_accept', 'accepted'),
            Action::make('comments_accept', 'rejected')
        ));
        
        $search = new Comments_Search($DB);
        $search->limit = $comment_limit;
        foreach(array('beforedate', 'afterdate', 'beforecomment', 'aftercomment', 'words', 'nodes', 'offset') as $search_setting) {
            $search_value = get($request, $search_setting, $this->$search_setting);
            $search->$search_setting = $search_value;
        }

        $comments_found = $search->find();
        
        $smarty->assign('comments', $comments_found->comments);
        
        // Comments are shown newest-first. Next means forward in time, thus
        // backwards in the list of comments
        $next = false;
        if ($this->offset > 0) {
            $next = clone $this;
            $next->offset = max(0, $next->offset + $comment_limit);
        }
        
        $prev = false;
        if ($comments_found->more) {
            $prev = clone $this;
            $prev->offset += $comment_limit;
        }
        $smarty->assign('navigation_actions', compact('next', 'prev'));
        
        $page_requisites = new Page_Requisites();
        $page_requisites->add_managed_css('comments.css');
        $smarty->assign('page_requisites', $page_requisites);
        $result->use_template('comments_list.tpl');
    }
}



