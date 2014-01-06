
<?php
/** @package Aquarius.frontend.shop
  */

function smarty_block_listcart($params, $content, &$smarty, &$repeat) {
    static $cart;
    static $index;
    static $total_price;
    Log::debug("cart");Log::debug($cart);
    if ($repeat) {
        $cart = $_SESSION["shop"]["order"];
        $index = 0;
        $total_price = 0;
    }

    if (is_array($cart) && count($cart) > 0) {
        $order = array_shift($cart);
        $var = get($params, 'var', 'entry');
        $smarty->assign($var,$order);
        $total_price += $order["total_price"];
        $smarty->assign("index",$index);
        $repeat = true;
        $index++;
    } else {
        $repeat = false;
        $smarty->assign("order_total_price",$total_price);
        $smarty->assign("orde_total_products",$index);
    }

    return $content;

}

?>