<?php

/** Accept or reject comments
  */
class action_comments_accept extends ModuleAction implements ChangeAction {
    var $modname = "comments";
    var $props = array('class', 'new_status');

    function valid($user) {
      return (bool)$user;
    }
    
    function get_title() {
        return new Translation($this->new_status == 'accepted' ? 'comments_accept' : 'comments_reject');
    }

    function get_icon() {
        return $this->new_status == 'accepted' ? 'picts/flag_0.gif' : 'picts/flag_1.gif';
    }
    
    function process($aquarius, $post, $result) {
        $selected = get($post, 'comment_select', array());
        $message = AdminMessage::with_line('ok', $this->new_status == 'accepted' ? 'comments_accepted' : 'comments_rejected');
        if (is_array($selected) && count($selected) > 0) {
            $result->add_message($message);
            foreach($selected as $comment_id) {
                $comment = DB_DataObject::factory('comment');
                $found = $comment->get($comment_id);
                if (!$found) throw new Exception("Comment with id '$comment_id' does not exist.");
                $comment->status = $this->new_status;
                or_die($comment->update(), "Unable to change comment '$comment_id' to status '$this->new_status'.");
                $message->add_html($comment->subject);
            }
        }
    }
}



