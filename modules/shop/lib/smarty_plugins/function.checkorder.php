<?php 
/** @package Aquarius.frontend.shop
  */

/** 
  *
  */

function occurence_in_attributes($attributes, $value) {
    foreach ($attributes as $attribute) {
        if (intval($attribute["id"]) === intval($value)) {
            return true;
        }
        Log::debug($attribute["id"]."==".$value);
    }
    return false;
}

function get_name_from_attribute($attributes, $value) {
    foreach ($attributes as $attribute) {
        if (intval($attribute["id"]) === intval($value)) {
            return $attribute["name"];
        }
    }
    return false;
}

/**
* This smarty function checks if the user wants to add/remove
* Something to/from the cart. The names for the input fields used
* in this function, must be used in the template, if the mechanism shall function
*/
function smarty_function_checkorder($params, &$smarty) {
    static $staticcart;
    global $lg;
    
    //debug
    if (requestvar("clear")) {
        $_SESSION["shop"]["order"] = array();
    }
   
 

    //some other template already called this function. all variables are assigned. return!
    $cart = $staticcart;
    if (!empty($staticcart)) {
        $smarty->assign("cart",$staticcart);
        return;
    }


    //do we have to change the count of an item?
    $changecount = get(requestvar('shop'),'changecount',false);
    if ($changecount) {
        foreach ($changecount as $index => $item) {
            //print_r($cart[$index]);
            if ($index <= count($_SESSION["shop"]["order"]) && !empty($_SESSION["shop"]["order"][$index])) {
                $oldcount = $_SESSION["shop"]["order"][$index]["count"];
                $price = $_SESSION["shop"]["order"][$index]["price"];
                $_SESSION["shop"]["order"][$index]["count"] = $item;
                $_SESSION["shop"]["order"][$index]["total_price"] = $price * $item;
            }
        }

    }

    //do we have to remove an item from the cart?
    $remove = get(requestvar('shop'),'remove',false);
    if($remove) {
        foreach ($remove as $index => $item) {
            if ($index <= count($_SESSION["shop"]["order"])) {
                unset($_SESSION["shop"]["order"][$index]);
                Log::debug($index." unsetted");
            }
        }
    }

    //check if there is a product to be added to the cart. if not return here
    $shop = requestvar('shop');
    $insert = get($shop,"addproduct",false);
    if (!$insert) {
        $staticcart = $_SESSION["shop"]["order"];
        $smarty->assign('cart',$_SESSION["shop"]["order"]);
        Log::debug("no product to add");
        return;
    }

    //load the product node if there is any
    $product_node = DB_Node::get_node(get($params, 'node', false));
    if ($product_node  == false) { 
        //$smarty->assign('cart',$_SESSION["shop"]["order"]);
        Log::debug("no valid product node");
        return;
    }
    Log::debug("loading product to session");
    $products = $product_node->get_content();
    $products->load_fields();

    //add new product to cart
    //load the product specific attributes
    $attributes = $smarty->get_template_vars("attributes");
    if (!$attributes) {
        $mapping_node = DB_DataObject::factory('node_mapping');
        $attributes = $mapping_node->get_attribute_selection($product_node->node_id);
        Log::debug("please use the 'checkorder' plugin after the 'activecategory' plugin, loading the attributes twice is a big overhead. So: never get into this if...");
    } 

    $order = array();
    //load default product values
    //default 1 if field has no or some strange value
    $order["product_id"] = $product_node->id;
    $order["count"] = get($shop, 'count',1);
    if (!is_numeric($order["count"])) $order["count"] = 1;

    //user/product specific
    $order["discount"] =  $products->discount;
    //user specific::TODO

    //use quantity discount values if necessary, use pricetag of attribute if set
    //getprice of attribute (attribute node); check if price changes, use this price else:
    $price_sensible_attributes = array();
    $price_sensible_attribute = array();
    $i = 0;
    foreach($attributes as $attribute) {
        foreach($attribute as $key => $value) {
            if (is_array($value) && !empty($value["price"])) {
                //store id ($value["id"]), name ($value["name"]) and price
                $price_sensible_attributes[$i] = array($value["id"],$value["name"],$value["price"]);
                //Log::debug($value["id"]);Log::debug($shop[PRICE_SENSIBLE_ATTRIBUTE]);
                if ($value["id"] == $shop[PRICE_SENSIBLE_ATTRIBUTE]) {
                    $price_sensible_attribute["id"] = $value["id"];
                    $price_sensible_attribute["name"] = $value["name"];
                    $price_sensible_attribute["price"] = $value["price"];
                }
                $i++;
            }
        }
    }


    //Log::debug("price sensible: ");Log::debug($price_sensible_attribute);
    if (get($price_sensible_attribute,"price",0) != 0) {
        $price = $price_sensible_attribute["price"];
    } else {
        $price = $products->price;
    }

    if ($products->discount > 0) $price = $price * (1-($products->discount/100));

    if ($products->qdiscount_count > 0 && $products->qdiscount_count <= $order["count"]) {
        $order["price"] =  $price * (1-($products->qdiscount_percent/100)); 
    } else {
        $order["price"] =  $price;
    }

    $order["total_price"] =  $order["price"] * $order["count"];//price * discount * count
    $order["name"] = $products->name;
    $order["description"] = $products->description;

    //unit
    $order["unit"] = $attributes["unit"]["name"]; //attributes.unit (standard attribute)

    //color
    if (occurence_in_attributes($attributes["color"],$shop["color"])) {
        $order["color_id"] = $shop["color"];
        $order["color"] = get_name_from_attribute($attributes["color"],$shop["color"]);
    } else {
        $smarty->assign("error_occured",1);
        $smarty->assign("error_message","no valid product color chosen");
        Log::debug("no valid product color given");
        return;
    }

    //size
    if (occurence_in_attributes($attributes["size"],$shop["size"])) {
        $order["size_id"] = $shop["size"];
        $order["size"] = get_name_from_attribute($attributes["size"],$shop["size"]);
    } else {
        $smarty->assign("error_occured",1);
        $smarty->assign("no valid product size chosen");
        Log::debug("no valid product size given");
        return;
    }

    $order["product_attributes"] = $attributes;

    //special attributes like text
    //TODO

    //number of entries actually in the cart.
    $order["index"] = count($_SESSION["shop"]["order"]);



    $_SESSION["shop"]["order"][] = $order;

    $cart = $_SESSION["shop"]["order"];
    $staticcart = $_SESSION["shop"]["order"];

    $cart['new'] = 1;
    //Log::debug("cart");Log::debug($cart);

    $smarty->assign("cart",$cart);
    return;
}
?>