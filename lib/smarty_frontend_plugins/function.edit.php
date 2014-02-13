<?php
/** Display an edit-button for a node
  * <pre>
  * Parameters:
  *  node: The node to be edited, $node template variable by default
  * </pre>
  */
function smarty_function_edit($params, &$smarty) { 
    global $direct_edit;
    if(!$direct_edit) {
        return "";
    }
    require_once('lib/db/Users.php');
    require_once('lib/db/Node.php');
    require_once("lib/action.php");
    require_once("lib/adminaction.php");
    
    $nodeid = get($params, 'node',$smarty->get_template_vars('node'));
    $node = db_Node::get_node($nodeid);
    
    $lg = $smarty->get_template_vars('lg');

    $editlink = "";

    // Action that closes the popup and refreshes the page
    $closeaction = Action::make("frontendedit","close");

    // Try to create an edit action (User might not have permission to edit the desired node)
    $editaction = Action::make("contentedit", "edit",$node->id,$lg);

    // Build
    if ($editaction) {
        $editlink = '<a class="edit-link" href="#" onclick="window.open(\'/aquarius/admin/admin.php?lg='.$lg.'&amp;'.urlencode(str($closeaction)).'&amp;'.urlencode(str($editaction)).'\',\'directedit\',\'height=600,width=900,top=100,left=200,status=yes,resizable=yes,scrollbars=yes\'); return false;">
            <img src="/aquarius/admin/buttons/fe_edit.gif" alt="edit" title="edit"/>
        </a>';
    }
    return $editlink;
}
?>