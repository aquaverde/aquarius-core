<?php 
/** @package Aquarius.frontend
  */

function javascript_encode($array) {
    if (!is_array($array)) return "{}";
    $res = "new Array("; $i = 0;
    foreach ($array as $key => $value) {
        if ($i != 0) $res .= ",";
        $res .= "{id:{$value['id']},name:'{$value['name']}',price:{$value['price']}}";
        $i++;
    }
    $res .= ")";
    return $res;
}



function smarty_function_calcprice($params, &$smarty) {
    global $lg;
    $discount = doubleval(get($params, 'discount',0));
    $qdiscount_count = intval(get($params, 'qdiscount_count',0));
    $count_items = intval(get($params, 'count_items',1));
    $qdiscount_percent = doubleval(get($params, 'qdiscount_percent',0));
    $qdiscount_value = doubleval(get($params, 'qdiscount_value',0));
    $price = doubleval(get($params, 'price',0));
    if ($price == 0) throw new Exception("got pricevalue zero, product price must be set");
    $attributes = get($params, 'attributes',array());

    $price_sensible_attributes = array();
    $i = 0;
    foreach($attributes as $attkey => $attribute) {
        foreach($attribute as $key => $value) {
            if (is_array($value) && $attkey == PRICE_SENSIBLE_ATTRIBUTE && !empty($value["price"])) {
                //store id ($value["id"]) and name ($value["name"])
                $price_sensible_attributes[$i] = array("id"=>$value["id"],"name"=>$value["name"],"price"=>$value["price"]);
                $i++;
                Log::debug("calc1");
                Log::debug($value);
            }
        }
    }
    Log::debug("calculate");
    Log::debug($price_sensible_attributes);
    Log::debug($attributes);
    //TODO
    //->pass all values such that a javascript function can change the prices.
    if (is_array($price_sensible_attributes)) {
        $smarty->assign("attr_price_sensible",PRICE_SENSIBLE_ATTRIBUTE);
        $attr_prices = javascript_encode($price_sensible_attributes);
        Log::debug($attr_prices);
        $smarty->assign("attr_prices",$attr_prices);
    }
    //->take the correct price for the default chosen price senstitive attribute.

    if ($discount != 0) $discount /= 100;
    if ($qdiscount_percent) $qdiscount_percent /= 100;
    $qdiscount_eff = ($count_items > 0 && $count_items >= $qdiscount_count) ? (1 + $qdiscount_percent) : 1;
    $total_price = $price * (1-$discount) * $qdiscount_eff;

    Log::debug("price1: ".$price);
    Log::debug("price: ".$price." - ".$price=(1-$discount)." - ".$total_price);
    $smarty->assign("total_price",$total_price);
    return;
}
?>