<?php
/** Represent actions in the backend interface.
 * @package Aquarius.backend
 */
require_once "lib/action.php";
 
class AdminAction extends Action {
  
  /** Check for a logged-in user and delegate permission check to action implementation */
  function permit() {
    $user = db_Users::authenticated();
    return (bool)$user && $this->permit_user($user); // Delegate
  }
  
  /** Check that user is authorized.
    * Must be overriden by subclasses, always returns false.
    */
  function permit_user($user) {
    return false;
  }

  /** Get a smarty container for the backend */
  function get_smart() {
      global $aquarius;
      return $aquarius->get_smarty_backend_container();
  }
  
}


