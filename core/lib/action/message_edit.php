<?
abstract class action_message_edit extends AdminAction {
    /** Superadmins only */
    function permit_user($user) {
      return $user->isSuperadmin();
    }

    function load_message($message_id) {
        var_dump(DB_DataObject::staticGet('message', $message_id));
        return DB_DataObject::staticGet('message', $message_id);
    }
}

/** Save a message
  * action parameter 'message_id' may be an id of an existing message to overwrite it, or the string 'new' to create a new message. */
class action_message_edit_save extends action_message_edit implements ChangeAction {
    var $props = array('class', 'command');
    function get_title() {
        return new Translation('message_edit_save');
    }
    function process($aquarius, $post, $result) {
        $infomessage = new AdminMessage('info');
        foreach($post['messages'] as $message_id => $new_text) {
            $new = $message_id == 'new';
            if ($new) $message = DB_DataObject::factory('message');
            else      $message = DB_DataObject::staticGet('db_message', $message_id);
            $new_text = trim($new_text);
            if ($message->text != $new_text) {
                if (strlen($new_text) == 0) {
                    $message->delete();
                    $infomessage->add_line('message_removed', $message->get_title());
                } else {
                    $message->text = $new_text;
                    if ($new) {
                        $message->insert();
                        $infomessage->add_line('message_added', $message->get_title());
                    } else {
                        $message->update();
                        $infomessage->add_line('message_saved', $message->get_title());
                    }
                }
            }
        }
        if ($infomessage->has_parts()) $result->add_message($infomessage);
    }
}

class action_message_edit_list extends action_message_edit implements DisplayAction {

    var $props = array('class', 'command');

    function get_title() {
        return new Translation('message_edit_list');
    }

    function process($aquarius, $request, $smarty, $result) {
        $message_proto = DB_DataObject::factory('message');
        $empty_message = clone $message_proto;
        $empty_message->message_id = 'new';
        $message_proto->find();
        $smarty->assign("edit_messages", $message_proto);
        $smarty->assign("empty_message", $empty_message);
        $smarty->assign('actions', array(
            Action::make('message_edit', 'save'),
            Action::make('cancel')
        ));
        $result->use_template('message_edit.tpl');
    }
}
?>