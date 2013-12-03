<?php
/** Transient order used during the order process
 * Transient orders use current pricing and discount data to calculate totals.
 */
require_once 'DB/DataObject.php';

class Shop_Transient_Order {
    
    /** cart data  */
	var $items = array();
    var $charges = array();

    /** Calculate surcharge based on the current total */
    function calculate_charge($charge, $charge_percent) {
        $charge = Shop::parse_currency($charge);
        $charge_percent = floatval($charge_percent);
        if ($charge_percent > 0) {
            $cart_total = $this->cart_total();
            $total = $cart_total['total'];
            $add_charge = intval($total * $charge_percent / 100);
            $charge += Shop::parse_currency($add_charge);
        }
        return $charge;
    }

    /** Add a charge to the order
      * @param $title the title text of the charge
      * @param $charge Fixed charge amount
      * @param $charge_percent Percent of previous total to charge
      * @param $limit apply charge only if subtotal below $limit, default false means no limit.
      * @param $message message to display along the charge
      */
    function add_charge($title, $charge, $charge_percent = 0, $limit=false, $message='') {
        $charge = $this->calculate_charge($charge, $charge_percent);
        $charge_object = DB_DataObject::factory('shop_order_charge');
        $charge_object->name = $title;
        $charge_object->value = $charge;
        $charge_object->limit = $limit;
        $charge_object->message = $message;
        $this->charges[] = $charge_object;
    }

    /** List of charges that apply */
    function charges() {
        $total = $this->total();
        $charges = array();
        foreach ($this->charges as $charge) {
            // Hack: if total after discount is above charge's limit, add the charge, but with zero value, so that it is visible
            if ($charge->limit && $charge->limit <= $total['total']) {
                $charge = clone $charge;
                $charge->value = 0;
                $charge->message = '';
            }
            $charges[] = $charge;
        }
        return $charges;
    }
    
    function get_user() {
        if (isset($this->user)) return $this->user;
        return db_Fe_users::authenticated();
    }

    /** Calculate price and discount
      * @return hash with subtotal, discount, discount_rate and total */
    function total() {        // Collect prices from order items
        $subtotal = 0;
        foreach($this->items as $item) $subtotal += $item->total_price();
        $total = $subtotal;

        // Calculate discount
        $discount_rate = false;
        $discount = 0;
        if(defined('SHOP_DISCOUNT_GROUP_ID')) {
            // See whether user is in discount group
            $user = $this->get_user();

            if ($user && $user->in_group(SHOP_DISCOUNT_GROUP_ID)) {
                // Get discount rate
                $admin_node = db_Node::get_node('shop_admin');
                if ($admin_node) {
                    $discount_rate = $admin_node->get_discount_percent();
                    if ($discount_rate > 0) {
                        // Calculate discounted total
                        $discount_mul = $subtotal * (100 - $discount_rate);

                        // Rounding down the total -- not the discount -- for customer satisfaction
                        $total = intval($discount_mul / 100);
                        $total = Shop::parse_currency($total);

                        // The resulting discount
                        $discount = $total - $subtotal;
                    }
                }
            }
        }

        return compact('subtotal', 'discount', 'discount_rate', 'total');
    }

    /** Get total price of all items in cart
      * Returns array with this values:
      *  subtotal: Total before discount
      *  discount_rate: discount rate in percent, false if this user does not get a discount
      *  discount: Discount amount, zero if none
      *  total: Sum after discount, including charges
      *  may_order: Whether the cart can be ordered */
    function cart_total() {
        extract($this->total());

        $charges = $this->charges();
        foreach($charges as $charge) {
            $total += $charge->value;
        }

        $may_order = $subtotal > SHOP_ORDER_MINIMUM;
        return compact('subtotal', 'discount_rate', 'discount', 'total', 'may_order', 'charges');
    }

    /** Create a finalized copy of this order */
    function make_finalized($user, $address, $pay_method_node) {
        // Deep-copy the current order information
        $order = unserialize(serialize($this));
        
        $order->address = $address;
        $order->user = $user;
        $order->id = $user->id.'-'.strtoupper(uniqid());

        $order->paymethod = $pay_method_node;
        $charge_title = translate('payment_charge');
        $order->add_charge($charge_title.': '.$pay_method_node->title, $pay_method_node->surcharge, $pay_method_node->surcharge_percent);

        return $order;
    }

    /** Create a persistent copy from this order
      * Only finalized orders may be made permanent */
    function make_persistent($status) {
        if (!$this->id) throw new Exception('Order missing ID');
    
        $order = new Shop_order();

        $order->id = $this->id;
        $order->status = $status;
        $order->user_id = $this->user->id;

        $order->order_date = time();
        $order->client_ip_address = $_SERVER['REMOTE_ADDR'];

        $this->address->insert(); // Create a copy of the address
        $order->address_id = $this->address->id;
        $order->paymethod_id = $this->paymethod->node_id;

        $order->address = $this->address;
        $order->items = $this->items;
        $order->charges = $this->charges();

        $total = $this->cart_total();
        $order->discount = $total['discount'];
        $order->total_price = $total['total'];

        $result = $order->insert();
        if (!$result) throw new Exception("Failed inserting order $order->id");
        return $order;
    }
}



