<?php
/**
 * Table Definition for shop_order_item
 */
require_once 'DB/DataObject.php';

/** Order data for an item.
  * Product data is stored in this object instead of loaded from product since the product and its attributes can change during or after an order. */
class Shop_order_item extends db_Shop_order_item {

    /** List of Shop_order_item_attribute objects */
    var $attributes = array();

    /** Create order information for a product
      * @param $node the product node
      * @param $count how many to order
      * @param $attributes Attribute list for this product
      * @param $selected_properties assoc array of the form attribute_name => property_id (ex. attribute_color=>123)
      * @param $additional_properties assoc array with attribute names as key (ex. attribute_text=>'Hello world')
      * Price taken either from the product's 'price' field or from a property if it has 'price' set
      */
    static function create($product_node, $count, $attributes, $selected_properties, $additional_properties) {

        // Create the item and fill with values
        $order_item = new Shop_order_item();
        $order_item->product_id = $product_node->id;
        $order_item->count = $count;

        // Add the selected properties
        foreach($selected_properties as $attribute_name => $property_id) {
            if (!isset($attributes[$attribute_name])) throw new Exception('Missing attribute '.$attribute_name);
            extract($attributes[$attribute_name]);
            if (!isset($properties[$property_id])) throw new Exception('Attribute $attribute_name does not have property with id $property_id for product $product_node->id');
            $selected_settings = $properties[$property_id];
            $item_attribute = Shop_order_item_attribute::create($selected_settings['settings']);

            $order_item->attributes[$attribute->id] = $item_attribute;
        }

        foreach($additional_properties as $name => $property) {
            $item_attribute = new Shop_order_item_attribute();
            $item_attribute->attribute_name = $name;
            $item_attribute->value = $property;
            $order_item->attributes[$name] = $item_attribute;
        }
        $order_item->update_fields();
        return $order_item;
    }

    /** Update title, code and other fields with current values */
    function update_fields() {
        $this->title = $this->product_title();
        $this->price = $this->product_price();
        $product = db_Node::get_node($this->product_id);
        $this->code = $product->get_code();
    }

    /** Saves attributes as well */
    function insert() {
        $this->update_fields();
        $result = parent::insert();
        foreach($this->attributes as $item_attribute) {
            $item_attribute->shop_order_product_id = $this->id;
            $result = $result && $item_attribute->insert();
        }
        return $result;
    }

    /** Loads attributes as well */
    function fetch() {
        $result = parent::fetch();
        if ($result) {
            $item_attribute = DB_DATAOBJECT::factory('Shop_order_item_attribute');
            $item_attribute->shop_order_product_id = $this->id;
            $item_attribute->find();
            $this->attributes = array();
            while($item_attribute->fetch()) {
                $this->attributes[] = clone $item_attribute;
            }
        }
        return $result;
    }
        
    function delete() {
        parent::delete();
        foreach ($this->attributes as $attribute) {
            $attribute->delete();
        }
    }

    /** Load current product title
      * This takes the current title of the product, use only in transitory orders */
    function product_title() {
        $product = db_Node::get_node($this->product_id);
        if ($product) return $product->get_title();
        return $this->title;
    }

    /** Get the price in cents for one item
      * The price may vary depending on the attributes. */
    function product_price() {
        $product = db_Node::get_node($this->product_id);
        if ($product) {
            foreach ($this->attributes as $attribute) {
                $new_price = $attribute->new_price();
                if ($new_price !== false) return $new_price;
            }
            return Shop::parse_currency($product->get_price());
        }
        return $this->price;
    }

    /** Get the cent price for all items together */
    function total_price() {
        return $this->price * $this->count;
    }

    /** Compare another order item to this item
      * @return true if product_id and attributes are the same
      * Count is not relevant in this comparison. */
    function same($other_item) {
        if ($this->product_id != $other_item->product_id) return false;
        foreach($this->attributes as $attribute_id => $attribute) {
            $other_attribute = get($other_item->attributes, $attribute_id);
            if (!$other_attribute) return false;
            if ($attribute->property_id != $other_attribute->property_id) return false;
        }
        return true;
    }
}
