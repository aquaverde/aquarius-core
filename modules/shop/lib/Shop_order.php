<?php
/**
 * Table Definition for shop_order
 */
require_once 'DB/DataObject.php';

class Shop_order extends db_Shop_order
{

    /** related data, automatically loaded on fetch  */
	var $items = array();
    var $charges = array();

    /** Get total price of all items in cart
      * Returns array with this values:
      *  subtotal: Total before discount
      *  discount: Discount amount, zero if none
      *  total: Sum after discount */
    function cart_total() {
        $subtotal = 0;
        foreach($this->items as $item) $subtotal += $item->total_price();

        $discount = intval($this->discount);
        $charges = $this->charges;


        $total = intval($this->total_price);
        return compact('subtotal', 'discount_rate', 'discount', 'total', 'may_order', 'charges');
    }

    function get_address() {
        return $this->address;
    }

    function get_user() {
        $user = DB_DataObject::factory('fe_users');
        if ($user->get($this->user_id)) return $user;
        else return false;
    }

    function get_paymethod() {
        return db_Node::get_node($this->paymethod_id);
    }

    function set_sequence_nr() {
        global $DB;
        // MySQL can't do this:
        // $DB->query("UPDATE shop_order SET shop_order.sequence_nr = (SELECT max(sequence_nr) FROM shop_order) + 1 WHERE id = '$this->id'");
        // Do it in PHP, race condition ahead!
        $max = $DB->singlequery('SELECT max(sequence_nr) FROM shop_order');
        $this->sequence_nr = $max + 1;
        $DB->query("UPDATE shop_order SET shop_order.sequence_nr = $this->sequence_nr WHERE id = '$this->id'");
    }

    /** Saves items as well */
    function insert() {
        $result = parent::insert();
        if ($result) {
            foreach($this->items as $item) {
                $item->order_id = $this->id;
                $result = $result && $item->insert();
            }
            foreach($this->charges as $charge) {
                $charge->order_id = $this->id;
                $result = $result && $charge->insert();
            }
        }
        return $result;
    }
    
    /** Loads items as well */
    function fetch() {
        $result = parent::fetch();
        if ($result) {
            $item = new Shop_order_item();
            $item->order_id = $this->id;
            $item->find();
            $this->items = array();
            while($item->fetch()) {
                $this->items[] = clone $item;
            }

            $charge = DB_DataObject::factory('Shop_order_charge');
            $charge->order_id = $this->id;
            $charge->find();
            $this->charges = array();
            while($charge->fetch()) {
                $this->charges[] = clone $charge;
            }

            $address = DB_DataObject::factory('fe_address');
            $address->get($this->address_id);
            $this->address = $address;
        }
        return $result;
    }

    function delete() {
        parent::delete();
        foreach ($this->items as $item) {
            $item->delete();
        }
        foreach ($this->charges as $charge) {
            $charge->delete();
        }
        if ($this->address) $this->address->delete();
    }

    /** DB_DATAOBJECT override because the class generator erroneously uses the autoincremented field 'sequence_nr' instead of the primary key 'id' as key. */
    function keys() {
        return array('id');
    }
}



