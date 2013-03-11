<?php 
/** @package Aquarius.frontend.shop
  */

/** 
  * Add a product attribute to the shop_order_attribute table
  * and create the attribute type ($dborder_attr->get_type($name))
  * if it does not exist.
  * 
  */
function add_attribute($order_product_id, $name, $node_id, $title) {
        $dborder_attr = DB_DataObject::factory('shop_order_attribute');
        $dborder_attr->order_product_id = $order_product_id;
        $dborder_attr->type_id = $dborder_attr->get_type($name);
        $dborder_attr->value = $title;
        $dborder_attr->node_id = $node_id;
        $dborder_attr->insert();
        Log::debug($dborder_attr);
        return $dborder_attr;
}


/** 
  * Add the order from the session to the database. the session information
  * should be fully consistant. it is not fully checked here 
  * (e.g. the paymethod/deliverymethod should be defined)
  * 
  */
function smarty_function_checkpayment($params, &$smarty) {
    global $lg;
    Log::debug("add order to database");

    //get cart or return if cart is empty
    $cart = $_SESSION["shop"]["order"];
    $dborder_id = 0;
    if (count($cart) == 0) {
        Log::debug("Cart empty, nothing to do");
        return;
    }

    //add order to database
    //insert new order entity
    $dborder = DB_DataObject::factory('shop_order');
    $dborder->count = count($cart);
    $dborder->user_id = $_SESSION["shop"]["user_id"];
    $dborder->insert();
    Log::debug($dborder);
    $dborder_id = $dborder->id;

    if ($dborder_id <= 0) {
        Log::debug("Error inserting order entity");
        return;
    }
    //add charges
    $total_price = 0;
    $charges = $_SESSION["shop"]["charges"];
    foreach ($charges as $charge) {
        $dborder_charges = DB_DataObject::factory('shop_order_charges');
        $dborder_charges->order_id = $dborder_id;
        $dborder_charges->name = $charge["name"];
        $dborder_charges->value = $charge["value"];
        $dborder_charges->insert();

        $total_price += $charge["value"];
        Log::debug($dborder_charges);
    }

    //add products
    foreach ($cart as $product) {
        $dborder_product = DB_DataObject::factory('shop_order_product');
        $dborder_product->order_id = $dborder_id;
        $dborder_product->product_id = $product["product_id"];
        $dborder_product->price = $product["price"]; //{*! inkl discount*}
        $dborder_product->discount = $product["discount"];
        $dborder_product->total_price = $product["total_price"]; 
        $dborder_product->count = $product["count"];
        $dborder_product->insert();
        Log::debug($dborder_product);

        $order_product_id = $dborder_product->id;

        //insert attribute entity for each attribute defined (use fixed set here).
        //add size
        add_attribute($order_product_id, "size", $product["size_id"], $product["size"]);
        //color
        add_attribute($order_product_id, "color", $product["color_id"], $product["color"]);

        $total_price += $product["total_price"];
    }
    $dborder->paymethod_id = get($_SESSION["shop"]["methods"],"paymentnode");
    $dborder->delivermethod_id = get($_SESSION["shop"]["methods"],"deliverynode");
    $dborder->discount = 0;//default not existant, user dependent in future = $dborder->get_discout($dborder->user_id)
    $dborder->total_price = $total_price;

//     $time = time();
//     $dbtime = mktime(date('G',$time), date('i',$time), date('s',$time), date('m', $time),date('d', $time),date('Y', $time));
    $dbtime = date("c");
    $dborder->order_date = $dbtime;

    $dborder->update();
    Log::debug($dborder);

    //clear cart
    $_SESSION["shop"]["order"] = array();

    return;
}
?>