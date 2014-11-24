<?php 
/** Check the nodes given as parameters. 
  * The first node that has a dynform available will be assigned to the dynform variable.
  */
function smarty_function_find_dynform($params, $smarty) {
    foreach($params as $param) {
        $node = db_Node::get_node($param);
        if ($node) {

            $DL = new Dynformlib;
            
            $dynform = new db_Dynform ; 
            $dynform->node_id = $form_node->id ;
            $dynform_available = $dynform->find() ; 
            if ($dynform_available) {
                $smarty->assign("dynform", $node) ;
                break;
            }
        }
    }
}
