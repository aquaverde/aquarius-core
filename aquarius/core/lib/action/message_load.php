<?
/** Inject admin messages
  * When this action is executed, messages from the 'message' table are added to the list of messages to be displayed. */
class action_message_load extends AdminAction implements DisplayAction {
    /** all users permitted */
    function permit_user($user) {
      return (bool)$user;
    }

    function process($aquarius, $request, $smarty, $result) {
        $message = DB_DataObject::factory('message');
        $message->find();
        while($message->fetch()) {
            $result->add_message(AdminMessage::with_html('info', $message->text));
        }
    }
}

?>