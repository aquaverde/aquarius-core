<?php 
require_once 'lib/Shop_order.php';
require_once 'lib/Shop_order_item.php';
require_once 'lib/Shop_order_item_attribute.php';
require_once "lib/shop_transient_order.php";

/** Manage a Webshop in Aquarius
  *
  * This module adds a formtype that lets you categorize products by size, colour etc. The following terms are used:
  *   attribute:           A group of possible properties like colour, size &c.
  *   property:            One possible property, e.g. 'green' or 'XL'
  *   setting:             Additional per product per property information, like a picture of an XL-shirt
  *   attribute selection: Which attributes are relevant to a product category, say 'colour' is relevant to the group 'ball point pens', where size isn't.
  * You're likely to be confused now, and that's not your fault. The words were not wisely chosen, 'choosing' is too strong a word for how these words came to be used.
  *
  */
class Shop extends Module {

    var $register_hooks = array('menu_init', 'smarty_config', 'init_form', 'smarty_config_backend', 'smarty_config_frontend', 'frontend_page', 'daily');
    
    var $short = "shop";
    var $name  = "Shop Modul";
    
    function menu_init($menu, $lg) {
        $entry = new Menu('shop_menu', false,false, array(
            new Menu('shop_orders', Action::make('shop_order','show_orders')),
            new Menu('shop_users', Action::make('feuser', 'list', 'null', '0')),
            new Menu('shop_groups', Action::make('fegroup', 'list', 'null', '0'))
        ));
        $menu->add_entry('menu_root', 45, $entry);
    }

    function init_form($formtypes) {
        $formtypes->add_formtype(new Formtype_shop_attr('shop_attr', $this));
    }

    /** Get attribute settings for a given node.
      * The first parent node that has a field named after the constant SHOP_ATTRIBUTE_SELECTION_FIELD_NAME, supplies the list of attribute selectors in this field. */
    function attribute_settings($node, $require_active=false) {
        // Find attribute selection field in parents
        $attribute_selection = false;
        foreach(array_reverse($node->get_parents()) as $parent) {
            foreach ($parent->get_form()->get_fields() as $field) {
                if ($field->name == SHOP_ATTRIBUTE_SELECTION_FIELD_NAME) {
                    $selection_node = $parent;
                    $content = $selection_node->get_content();
                    $content->load_fields();
                    $attribute_selection = $content->{SHOP_ATTRIBUTE_SELECTION_FIELD_NAME};
                    break;
                }
            }
            if ($attribute_selection) break;
        }
        if ($attribute_selection === false) throw new Exception("Missing attribute selection for ".$node->idstr()."; there must be a parent node with a field named SHOP_ATTRIBUTE_SELECTION_FIELD_NAME=".SHOP_ATTRIBUTE_SELECTION_FIELD_NAME." pointing to selected attributes");

        $attribute_list = array();
        foreach($attribute_selection as $attribute) {
            $properties = array();
            foreach($attribute->children(array('inactive_self')) as $property) {
                $settings = DB_DataObject::factory('shop_node_attribute_settings');
                if ($node->id) {
                    $settings->node_id = $node->id;
                    $settings->property_node_id = $property->id;
                    $settings->find() && $settings->fetch();
                }
                if (!$require_active || $settings->active) {
                    $properties[$property->id] = compact('property', 'settings');
                }
            }
            $attribute_list[$attribute->id] = compact('attribute', 'properties');
        }
        return $attribute_list;
    }

    function frontend_interface() {
        require_once 'lib/shop_frontend.php';
        return new Shop_Frontend($this);
    }

    /** Tells frontend interface to process requests*/
    function frontend_page($smarty) {
        $interface = $smarty->get_registered_object('shop');
        $interface->process_requests($smarty);
    }

    /** Remove old, temporary orders */
    function daily() {
        $order = new Shop_order();
        $order->status = 'temporary';
        $order->whereAdd('order_date < '.(time() - SHOP_DELETE_TEMPORARY_ORDERS_DELAY));
        $found = $order->find();
        Log::info("Shop: cleaning out ".(int)$found." old temporary orders");
        while ($order->fetch()) {
            Log::debug('Deleting order '.$order->id);
            $order->delete();
        }
    }

    /** Parse a value into currency amount
    * @param $value the value to make a currency from
    * @param $in_parts return an array with thalers, cents and negative flag instead of cent amount. In this case the returned values are never negative. Example: -9.75 is returned as array(9, 75, true).
    * @return amount of cents
    *
    * Value may be an integer, string, or float:
    * <pre>
    *   integer: Interpreted as amount of cents
    *   string: Split at the first period into thalers and cents, other non-numeric characters are ignored
    *   float: multiplied by cent base to get cent amount. But DO NOT USE FLOATS TO STORE CURRENCY VALUES; they are inaccurate!
    * </pre>
    *
    * Output depends on shop configuration variables:
    * <pre>
    *   SHOP_CENT_DIGITS: how many places to assume after the comma, default two
    *   SHOP_CENT_UNIT: Smallest possible cent amount, default 5
    * </pre>
    *
    * Cents that are smaller than the cent unit are subtracted. So if unit is five, the value 49 is returned as 45. This function always rounds down.
    *
    * Examples, assuming two cent digits:
    * <pre>
    *   12345 => 123 thalers and 45 cents
    *   12349 => 123 thalers and 45 cents (assuming cent unit 5)
    *   "12345" => 12345 thalers
    *   "123.45" => 123 thalers and 45 cents
    *   "123.4" => 123 thalers and 40 cents
    *   "Please pay 123'456'789.45" => 123456789 thalers and 45 cents
    *   (float)1234.56 => 1234 thalers 55 cents (assuming cent unit 5)
    * </pre>
    *
    * You do not want to use amounts bigger than 2^31 cents
    */
    static function parse_currency($value, $in_parts=false) {

        $cent_digits = intval(defined('SHOP_CENT_DIGITS')?SHOP_CENT_DIGITS:2);
        $cent_base = pow(10, $cent_digits);
        $cent_unit = intval(defined('SHOP_CENT_UNIT')?SHOP_CENT_UNIT:5);
        
        if (!is_int($value) && !is_float($value)) {
            // Coerce to string
            $value = str($value);

            // Remove uninteresting characters
            $value = ereg_replace("[^0123456789.]", "", $value);

            // Parse it as float
            $value = floatval($value);
        }

        // Make ints out of floats
        if (is_float($value)) {
            $value = intval($value * $cent_base);
        }
        
        // Separate into thalers and cents (there's no integer division in PHP, we're screwed)
        $thalers = intval(($value - ($value % $cent_base)) / $cent_base);
        $pennies = ($value % $cent_base);
        $pennies = $pennies - ($pennies % $cent_unit);

        return $in_parts ? array(abs($thalers), abs($pennies), $value < 0) : ($thalers * $cent_base + $pennies);
    }

}

class Formtype_shop_attr extends Formtype {

    var $module;

    function __construct($name, $module) {
        parent::__construct($name);
        $this->module = $module;
    }

    /** Prepare shop attribute form field for editing */
    function pre_contentedit($node, $content, $formtype, $formfield, $valobject) {
        // All the template needs is the attributes with settings
        $valobject->attribute_settings = $this->module->attribute_settings($node);
    }

    /** Save changes to attribute settings */
    function post_contentedit($formtype, $field, $value, $node, $content) {
        global $DB;
        if (!$node->id) throw new Exception("Received invalid node, id '$node->id'");

        // Delete all settings for this node from DB
        $deletesettings = DB_DataObject::factory('shop_node_attribute_settings');
        $deletesettings->node_id = $node->id;
        $deletesettings->delete();
        
        // Process settings in POST
        $settings = $this->module->attribute_settings($node);
        foreach($settings as $name => $setting) {
            extract($setting);
            foreach($properties as $property_settings) {
                extract($property_settings);
                $post_settings = requestvar('shop_property_'.$property->id, array());
                $active_settings = requestvar('shop_property_'.$attribute->id.'_active', array());
                $newsettings = DB_DataObject::factory('shop_node_attribute_settings');
                $newsettings->node_id = $settings->node_id;
                $newsettings->property_node_id = $settings->property_node_id;
                if(in_array($property->id, $active_settings))
                    $newsettings->active = 1;
                else
                    $newsettings->active = 0;
                $newsettings->price = get($post_settings, 'price', null);
                $newsettings->picture = get($post_settings, 'picture', null);
                $newsettings->text = get($post_settings, 'text', null);
                $upload_info = get($_FILES, 'shop_property_'.$property->id.'_newfile');
                if ($upload_info) {
                    $upload_result = process_upload($upload_info, SHOP_PICTURE_FOLDER);

                    // Use the new file if the upload was sucessful
                    if ($upload_result['error'] == UPLOAD_ERR_OK) {
                        $newsettings->picture = $upload_result['new_name'];
                    }
                }
                
                if ($newsettings->id) throw new Exception("Settings to be inserted have an ID already");

                $newsettings->insert();
                
            }
        }

    }

}
