<?php 
/** @package Aquarius.frontend.shop
  */

/** 
  * This smarty function tries to get the set delivery and paymenet method.
  * 
  */
function smarty_function_getmethods($params, &$smarty) {
    //check if any method is set
    $methods = requestvar("shop");
    if (!is_array($methods) && !(is_numeric($_SESSION["shop"]["methods"]["paymentnode"]) && is_numeric($_SESSION["shop"]["methods"]["paymentnode"]))) {
        Log::debug("No method chosen, aborting");
        return;
    }

    //initialize the variables
    $_SESSION["shop"]["charges"] = array();
    $smarty->assign("method_error",false);
    $smarty->assign("methrod_message", "");

    //get the correct payment node
    $paymentnode = db_Node::get_node($methods["payment"]);
    if (!$paymentnode) {
        $paymentnode = db_Node::get_node($_SESSION["shop"]["methods"]["paymentnode"]);
    }
    Log::debug($paymentnode);
    if ($paymentnode) {
        if ($paymentnode->get_form()->title != PAYMENT_NODE_FORM) {
            Log::debug("No correct payment method given, aborting");
            $smarty->assign("methrod_message", "No correct payment method given, aborting");
            return;
    
        }
        $_SESSION["shop"]["methods"]["paymentnode"] = $paymentnode->id;
        $paymentnode = $paymentnode->get_content();
        $paymentnode->load_fields();
        if ($paymentnode->surcharge  > 0) {
            $key = count($_SESSION["shop"]["charges"]);
            $_SESSION["shop"]["charges"][$key]["name"] = $paymentnode->name;
            $_SESSION["shop"]["charges"][$key]["value"] = $paymentnode->surcharge;
        }
        $price += $paymentnode->surcharge;
    } else {
        $smarty->assign("method_error",true);
    }

    //get the correct deliverynode
    $deliverynode = db_Node::get_node($methods["delivery"]);
    if (!$deliverynode) {
        $deliverynode = db_Node::get_node($_SESSION["shop"]["methods"]["deliverynode"]);
    }
    Log::debug($deliverynode);
    if ($deliverynode) {
        if ($deliverynode->get_form()->title != DELIVERY_NODE_FORM) {
            Log::debug("No correct delivery method given, aborting");
            $smarty->assign("methrod_message", "No correct payment method given, aborting");
            return;
        }
        $_SESSION["shop"]["methods"]["deliverynode"] = $deliverynode->id;
        $deliverynode = $deliverynode->get_content();
        $deliverynode->load_fields();
        if ($deliverynode->surcharge  > 0) {
            $key = count($_SESSION["shop"]["charges"]);
            $_SESSION["shop"]["charges"][$key]["name"] = $deliverynode->name;
            $_SESSION["shop"]["charges"][$key]["value"] = $deliverynode->surcharge;
        }
        $price += $deliverynode->surcharge;
    } else {
        $smarty->assign("method_error",true);
    }

    //get all special charges
    $chargesnodes = db_Node::get_node(SHOP_CHARGES_NODE)->children();
    print_r($charges);
    $charges = array();
    foreach ($chargesnodes as $charge) {
        $charge = $charge->get_content();
        $charge->load_fields();
        if ($charge->surcharge  > 0) {
            $key = count($_SESSION["shop"]["charges"]);
            $_SESSION["shop"]["charges"][$key]["name"] = $charge->title;
            $_SESSION["shop"]["charges"][$key]["value"] = $charge->surcharge;
        }
        $price += $charge->surcharge;
        $charges[] = $charge;
    }

    //assign the found method-nodes to smarty variables
    $smarty->assign("charges_price",$price);
    $smarty->assign("payment",$paymentnode);
    $smarty->assign("delivery",$deliverynode);
    $smarty->assign("charges",$charges);

    return;
}
