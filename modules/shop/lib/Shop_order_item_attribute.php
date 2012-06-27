<?php
require_once 'DB/DataObject.php';

/** Representation of an item attribute setting.
  * This is used to add properties like 'colour: green' to a shop item.
  * For the attribute (colour), and the property (green) the immediate string values are saved in the fields attribute_name and value.
  * References to the original attribute node and the property node are saved as attribute_id and property_id, but these are not to be trusted. Nodes cannot be trusted to exist or mean the same thing in the long term (imagine a setting node being renamed from 'green' to 'blue' and you will have no trouble to foresee angry customers if this change affects completed orders.) But there is also a reason to use the node references in the short term (while the shopping cart is in the session), because that allows changing language, which is not possible after saving string values like 'grün'.
  *
  * Attribute name is taken from the attribute content's 'name' field, and the value from the property content's 'title' field.
  */
class Shop_order_item_attribute extends db_Shop_order_item_attribute {

    /** Create an attribute setting
      * @param $setting the chosen setting for this item
      * @return an attribute setting for given $item
      *
      * Attribute and property are determined from the given setting.
      */
    static function create($setting) {
        $creating = new self();

        $property = db_Node::get_node($setting->property_node_id);
        if (!$property) throw new Exception("Unable to load property node for setting $setting->id");
        $attribute = $property->get_parent();

        $creating->attribute_id   = $attribute->id;
        $creating->property_id    = $property->id;
        $creating->setting_id     = $setting->id;

        $creating->update_fields();

        return $creating;
    }

    function get_referenced_node($field) {
        if (isset($this->$field)) {
            return db_Node::get_node($this->{$field});
        }
        return false;
    }

    /** Load attribute_settings
      * WARNING: Works only if setting_id is set
      */
    function get_setting() {
        return DB_DataObject::staticGet('db_shop_node_attribute_settings', $this->setting_id);
    }

    /** Return the attribute name
      * Loaded from the attribute content field 'name' if available or the saved name. */
    function attribute_name() {
        $attribute = $this->get_referenced_node('attribute_id');
        if ($attribute) {
            $name = $attribute->get_name();
            if (strlen($name) > 0) return $name;
        }
        return $this->attribute_name;
    }


    /** Return the setting name
      * Loaded from the setting's content field 'title' if available or the saved value. */
    function property_name() {
        $property = $this->get_referenced_node('property_id');
        if ($property) {
            $name = $property->get_title();
            if (strlen($name) > 0) return $name;
        }
        return $this->value;
    }

    /** Whether this setting changes the price of the item
      * @return new price or false if this setting does not change the price
      * For a setting to change item prices, the attribute must have the field 'changesPrice' set, and the setting must have a nonzero field 'price'.
      *
      * WARNING: This works only if attribute_id and setting_id are set.
      */
    function new_price() {
        $attribute = $this->get_referenced_node('attribute_id');
        $setting   = $this->get_setting();
        if ($setting && $attribute->get_changesPrice()) {
            $price = Shop::parse_currency($setting->price);
            if ($price > 0) return $price;
        }
        return false;
    }

    /** Updates the fields attribute_name and value with current values */
    function update_fields() {
        $this->attribute_name = $this->attribute_name();
        $this->value          = $this->property_name();
        
    }

    /** Ensures the fields 'attribute_name' and 'value' use current values */
    function insert() {
        $this->update_fields();
        return parent::insert();
    }
}
