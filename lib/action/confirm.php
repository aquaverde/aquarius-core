<?
/** Used to display a confirmation dialog.
 * Example: Action::make(
 *            'confirm',
 *            Action::make('node', 'delete', $node->id),
 *            Action::make('nodetree', 'show'),
 *            'Delete '.$node->get_contenttitle().'?',
 *            'Do you want to delete '.$node->get_contenttitle().'?'
 *          )
 */
class action_confirm extends AdminAction implements DisplayAction {

  var $props = array('class', "yesAction", "noAction", "title", "message");

  /** Always permitted for logged-in users */
  function permit_user($user) {
    return (bool)$user;
  }

  function process($aquarius, $request, $smarty, $result) {

    // Parse the actions and reset the sequence counters
    $yes = false;
    if ($this->yesAction instanceof Action) {
        $yes = $this->yesAction;
    } else {
        if (strlen(trim($this->yesAction)) > 0) {
            $yes = Action::parse($this->yesAction);
            $yes->sequence();
        }
    }
    $no = false;
    if ($this->noAction instanceof Action) {
        $no = $this->noAction;
    } else {
        if (strlen(trim($this->noAction)) > 0) {
            $no = Action::parse($this->noAction);
            $no->sequence();
        }
    }

    $smarty->assign("title", $this->title);
    $smarty->assign("message", $this->message);
    $smarty->assign("yesAction", str($yes));
    $smarty->assign("noAction", str($no));
    $result->use_template('confirm.tpl');
  }
}
?>