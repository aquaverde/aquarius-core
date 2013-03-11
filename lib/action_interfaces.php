<?php 

/** This interface signifies that something may be treated as an action */
interface BasicAction {
}

/** Actions that change the state of the system implement this interface.
  * Change actions must be submitted in a POST request. It is an error to
  * include such actions in a GET request. */
interface ChangeAction {
    /** Perform the change
      * @param $aquarius Aquarius instance that will be changed
      * @param $post POST request variables
      * @param $result ChangeResult object where results of the action can be noted */
    function process($aquarius, $post, $result);
}

/** Actions that do not change the state of the system and do not require smarty templates implement this interface.
  * After this action no further actions will be processed.
  * Typical example for this kind of action include file delivery and HTTP redirection.
  */
interface SideAction {
    /** Process the action
      * @param $aquarius Aquarius instance for reference
      * @param $request Request variables */
    function process($aquarius, $request);
}

/** Actions that provide data to the client, this may be a XHTML page or an AJAX answer, implement this interface */
interface DisplayAction {
    /** Prepare smarty container 
      * @param $aquarius Aquarius instance for reference
      * @param $request Request variables
      * @param $smarty container that will be used for display
      * @param $result DisplayResult instance where the action can pass display parameters */
    function process($aquarius, $request, $smarty, $result);
}

/** Actions that change and/or display data. This is primarly intended for forms that validate input. Actions implementing this interface will decide in the processing method whether display is needed and signal this with the $display_result->use_template() method. Contrary to ChangeActions, ChangeAndDisplayActions may be initiated from GET, it is the responsability of the implementation to ensure changes are only performed on POST. */
interface ChangeAndDisplayAction {
    /** Since these actions are hybrids between ChangeAction and DisplayAction, they receive all parameters the process methods of those other actions receive. As long as the implementation of this action does not touch the $display_result parameter, it will not be displayed.
      * @param $aquarius Aquarius instance for reference
      * @param $post POST request variables
      * @param $smarty container that will be used for display
      * @param $change_result ChangeResult object where results of the action can be noted
      * @param $display_result DisplayResult instance where the action can pass display parameters */
    function process($aquarius, $post, $smarty, $change_result, $display_result);
}

/** Container for actions to add results during their processing */
class ActionResult {
    /** Messages to the user */
    var $messages = array();
    
    /** Actions that are to be added to the action stack */
    var $inject_actions = array();

    /** Add a message to be displayed to the user */
    function add_message($message) { $this->messages[] = $message; }

    /** Specify a display action that should be processed later (not recommended but required in some circumstances) */
    function inject_action($action) { $this->inject_actions[] = $action; }

}

/** Container for results of ChangeActions */
class ChangeResult extends ActionResult {

    /** Touched regions where cache must be updated */
    var $touched_regions = array();

    /** Notify system about changed state in a region
      * Possible regions:
      *  'content': force clearing of the frontend cache
      *  'db_dataobject': clear DB_DATAOBJECT cache (this is only required if already loaded dataobjects are reused during the same request)
      *  Node_Change_Info object: Object informing about changes to a node 
      */
    function touch_region($region) { $this->touched_regions[] = $region; }
}

/** Container for results of DisplayActions */
class DisplayResult extends ActionResult {
    /** Name of the template to be used for display */
    var $template = null;

    /** Whether to include the action in action history.
      * This means that the user should return to this view once other actions have completed.
      * This is false by default, but must be turned on for sideband requests (AJAX). */
    var $skip_return = false;

    /** What template to use for this */
    function use_template($template) { $this->template = $template; }

    /** Do not return user to this view */
    function skip_return() { $this->skip_return = true; }
}
