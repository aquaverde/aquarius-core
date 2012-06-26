<?
/** The cancel action
  * It does nothing, but can be used to create cancel buttons.
  * Optional parameters are title (defaults to 's_cancel'), and icon, no default
  */
class action_cancel extends Action implements DisplayAction {

  var $props = array('class');

  /** Always permitted */
  function permit() {
    return true;
  }

  function get_title() {
        if (isset($this->params[0])) return $this->params[0];
        return new Translation('s_cancel');
  }
  
  function get_icon() {
        if (isset($this->params[1])) return $this->params[1];
        return false;
  }

  function process($aquarius, $request, $smarty, $result) {
    // Do nothing, display nothing
  }
}
?>