<?php
/** Build list of parents and add an adapted version of the current action for each parent node
  * Sets the template variable 'path_parents' to the list of parents of the given node */
function smarty_function_path_parents($params, $smarty) {
    $lg      = $smarty->get_template_vars("lg");
    $node    = $smarty->get_template_vars("node");
    $action  = clone($smarty->get_template_vars("lastaction"));

    $parents = array();
    $my_parents = $node->get_parents((bool)$node->id);
    foreach ($my_parents as $parent) {
            $tuple = array( "action" => false, 
                            "title" => $parent->get_contenttitle($lg));
            if (isset($action->node_id)) {
                    $action->node_id = $parent->id;
                    if ($action->permit()) {
                            $tuple['action'] = clone($action);
                    }
            }
            $parents[] = $tuple;
    }
    $smarty->assign('path_parents', $parents);

}