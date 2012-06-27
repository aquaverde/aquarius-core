<?
class action_pointings extends AdminAction {
    
  var $props = array("class", "node", "target");
  
  /** FIXME: Always permits for logged-in users */
  function permit_user($user) {
    return (bool)$user;
  }
  
  function execute() {
      require_once "lib/db/Node.php";
              
      $messages = array();
      $smarty = get_smart();
              
              // load the node tree
              if ( empty($this->node)  )
              $rootnode = db_Node::get_root();
              else {
                      $rootnode =& DB_DataObject::factory('node');
                      $rootnode->get($this->node);
              }
                      
      $rootnode->load_childs();
      $nodelist = $rootnode->as_flattree();
              
              $smarty->assign("target", $this->target);
              $smarty->assign("nodelist", $nodelist);
              $smarty->tmplname = 'popup_pointing.tpl';
              
              return compact('messages', 'smarty');
      }
}
?>