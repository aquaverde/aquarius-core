<?
/** The 'empty' action
 */
class action_nothing extends Action {

  var $props = array('class');

  /** Always permitted */
  function permit() {
    return true;
  }

  function execute() {
    $messages = array();
    $smarty = false;
    return compact('messages', 'smarty');
  }
}
?>