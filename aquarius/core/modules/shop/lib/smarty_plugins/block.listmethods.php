
<?php
/** @package Aquarius.frontend.shop
  */

function smarty_block_listmethods($params, $content, &$smarty, &$repeat) {
    static $nodes;
    static $index;
    static $checkednode = 0;
    Log::debug("cart");Log::debug($cart);
    if ($repeat) {
        $method = str(get($params,"method"));
        switch ($method) {
        case "payment":
            $nodes = db_Node::get_node(SHOP_PAYMETHOD_NODE)->children();
            $checkednode = $_SESSION["shop"]["methods"]["paymentnode"];
            break;
        case "delivery":
            $nodes = db_Node::get_node(SHOP_DELIVERMETHOD_NODE)->children();
            $checkednode = $_SESSION["shop"]["methods"]["deliverynode"];
            break;
        case "charges":
            $nodes = db_Node::get_node(SHOP_CHARGES_NODE)->children();
            break;
        default:
            return;
        }
        Log::debug($nodes);
        $index = 0;
    }


    if (is_array($nodes) && count($nodes) > 0) {
        $node = array_shift($nodes)->get_content();
        $node->load_fields();
        if ($checkednode > 0) {
            $node->enabled = false;
        }
        if ($node->node_id == $checkednode) {
            $node->enabled = true;
        }

        $var = get($params, 'var', 'entry');
        $smarty->assign($var,$node);
        $smarty->assign("index",$index);
        $repeat = true;
        $index++;
    } else {
        $repeat = false;
    }
    return $content;

}

?>