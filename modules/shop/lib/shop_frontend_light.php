<?php 
class Shop_Frontend_Light {
    var $module_core;

    /** assoc array with results of operations such as add_to_cart */
    var $results = array();

    function __construct($core) {
        $this->module_core = $core;
    }

    /** Load attributes and settings for a product
      * Params:
      *   node: The node you want to load the attributes for
      *
      * Returns the loaded structure like this:
      * <code>
      *   array(
      *     'attribute_color' => array(
      *        'title' => 'Farbe'
      *        'changesPrice' => 1
      *        ...
      *        'settings' => array(
      *           123 => array(
      *             'title' => 'Blau'
      *             'picture1' => 'blue.gif'
      *             'price' => '1200'
      *             'picture' => 'blue_product.jpg'
      *           )
      *        )
      *     'attribute_something' => ...
      *   )
      * </code> */
    function load_attributes($params, $smarty) {
        $smarty->loadPlugin('smarty_modifier_shop_currency_format');

        // Load attributes for product
        $product = db_Node::get_node(get($params, 'product'));
        if (!$product) throw new Exception("Unable to load product node");
        $attributes = $this->module_core->attribute_settings($product, true);

        // Collect attributes and their settings
        $attribute_settings = array();
        foreach($attributes as $attribute_and_properties) {
            extract($attribute_and_properties);

            // Use  the attribute's content fields if they exist
            $attribute_values = array();
            $attribute_content = $attribute->get_content($smarty->get_template_vars('lg'));
            if ($attribute_content) $attribute_values = $attribute_content->get_fields();

            // Add properties and their settings
            $attribute_values['settings'] = array();
            foreach($properties as $property_and_settings) {
                extract($property_and_settings);
                $property_values = array();

                // Use the property's fields if they exist
                $property_content = $property->get_content($smarty->get_template_vars('lg'));
                if ($property_content) $property_values = $property_content->get_fields();

                // Read the settings values
                $settings_values = $settings->toArray();
                if (!empty($settings_values['price'])) $settings_values['price'] = smarty_modifier_shop_currency_format($settings_values['price']); // Hack: Format the price nicely

                // Combine property values settings
                $combined = array_merge($property_values, $settings_values);

                $attribute_values['settings'][$property->id] = $combined;
            }
            $attribute_settings[$attribute->name] = $attribute_values;
        }
        return $attribute_settings;
    }

    function set_last_product($params, $smarty) {
        $_SESSION['last_product'] = $smarty->get_template_vars('node');
    }
    
    function get_last_product() {
        return get($_SESSION, 'last_product');
    }

    /** get the result of an operation
      * params:
      *   operation: name of he opeartion
      * False is returned if there are no results, likely because the opeartion was not executed */
    function get_result($params) {
        return get($this->results, get($params, 'operation'), false);
    }
    
    /** Get shopping cart from session */
    function get_order() {
        if (!$_SESSION['shop_order'] instanceof db_Shop_order) $this->initialize_order() ; 
        return $_SESSION['shop_order'] ; 
    }
    
    function initialize_order() {
        $order = DB_DataObject::factory('Shop_order');

        $charges_node = db_Node::get_node('shop_charges');
        $charges = $charges_node->children(array('inactive'));
        foreach($charges as $charge) $order->add_charge($charge->get_title(), $charge->get_surcharge());

        $_SESSION['shop_order'] = $order;
    }

    function process_requests() {
        require_once('lib/db/Fe_users.php');

        if (isset($_REQUEST['add_to_cart'])) $this->add_to_cart();
        if (isset($_REQUEST['update_cart'])) $this->update_cart();
        if (isset($_REQUEST['shop_data'])) $this->data();
        if(isset($_REQUEST['shop_order'])) $this->order();
    }

    /** Process add_to_cart requests
      * Params in request:
      *   product_id: id of the product to be added
      *   attribute_*: id of property_id for each attribute
      *   property: array of additional properties
      *   product_count: How many products to order
      **/
    function add_to_cart() {
        $count = intval(requestvar('product_count', 0));
        
        // Load product and its active attributes
        $product = db_Node::get_node(requestvar('product_id'));
        if (!$product) throw new Exception("Unable to load product node");
        $attributes = $this->module_core->attribute_settings($product, true);

        // Build list of selected properties for attributes
        $selected_properties = array();
        foreach($attributes as $attribute_and_properties) {
            extract($attribute_and_properties);
            $property_id = requestvar($attribute->name);

            // Add the property if it's active for this product
            if ($property_id && isset($properties[$property_id])) {
                $selected_properties[$attribute->name] = $property_id;
            }
        }

        $additional_properties = requestvar('property');
        if (!is_array($additional_properties)) {
            $additional_properties = array();
        }

        $order_item = db_Shop_order_item::create($product, $count, $attributes, $selected_properties, $additional_properties);


        // Put product in cart, only if count > 0
        if ($order_item->count > 0) {
            $order = $this->get_order();
            $order->items[] = $order_item;
        }
    }
    
    /** Process update_cart requests
      * List of item indexes to be removed from cart is expected in 'remove' request variable, 'count' holds the new count for each item.
      **/
    function update_cart() {
        $order = $this->get_order();
        $newcount = requestvar('count');
        $remove = requestvar('remove');

        // Update count for each item
        if (is_array($newcount)) {
            foreach($order->items as $position => $item) {
                if (isset($newcount[$position])) $item->count = intval($newcount[$position]);
            }
        }

        // Set count to zero for items that are to be removed
        if (is_array($remove)) {
            foreach($remove as $position) {
                $order->items[$position]->count = 0;
            }
        }

        // Remove all items with invalid count
        foreach($order->items as $position => $item) {
            if ($item->count < 1) unset($order->items[$position]);
        }
    }

    function get_methods() {
        $parent = db_Node::get_node('shop_pay_methods');
        if (!$parent) throw new Exception("Missing shop_pay_methods node");
        $method_nodes = $parent->children(array('inactive'));
        $methods = array();
        $selected_method = get($_SESSION, 'pay_method', 0);
        foreach($method_nodes as $method_node) {
            $content = $method_node->get_content();
            $content->load_fields();
            if ($selected_method > 0) $content->selected = $method_node->id == $selected_method;
            else $content->selected = $content->enabled;
            $methods[$method_node->id] = $content;
        }
        return $methods;
    }

    /** Fetch address from session or initialize it */
    function get_address() {
        $address = get($_SESSION, 'shop_address');
        if (!$address) {
            $address = DB_DataObject::factory('shop_address');
            $_SESSION['shop_address'] = $address;
        }
        return $address;
    }

    /** Places the current order information under a unique id in the session
      * During checkout it is necessary to keep order information separate so that it will not be modified if the user changes something in the main shopping cart.
      * @return the frozen order (must keep its id to access the order again) */
    function freeze_order() {
        // Deep-copy the current order information
        $order = unserialize(serialize($this->get_order()));

        $order->id = strtoupper(substr(uniqid(), 3));
        $order->order_date = time();
        $order->customer_ip_address = $_SERVER['REMOTE_ADDR'];

        $order->address = $this->get_address();


        $pay_method = $_SESSION['pay_method'];
        $available_methods = $this->get_methods();
        $pay_node = $available_methods[$pay_method];
        if (!$pay_node) throw new Exception("Invalid pay_method $pay_method");
        $order->paymethod_id = $pay_node->node_id;
        $order->add_charge('Zahlungsmethode: '.$pay_node->title, $pay_node->surcharge, $pay_node->surcharge_percent);
        
        $_SESSION['frozen_orders'][$order->id] = $order;

        return $order;
    }

    function get_frozen_order($params) {
        $id = $params['order_id'];
        $order = get($_SESSION['frozen_orders'], $id);
        if (!$order) throw new Exception("No frozen order for order_id '$order_id'");
        return $order;
    }
    

    function data() {
        $fields = array('email', 'firma', 'lastname', 'firstname', 'address', 'zipcity', 'land', 'phone');
        $address = $this->get_address();
        foreach($fields as $field) {
            $address->$field = requestvar($field);
        }

        $_SESSION['pay_method'] = intval(requestvar('pay_method'));
    }

    function order() {
        $order = $this->get_frozen_order($_REQUEST);
        $sent = false;
        $already_sent = @$order->sent;
        if (!$already_sent) {
            $order->insert();
            $this->send_mails($order);
            $order->sent = true;
            $sent = true;
            $this->initialize_order();
        }
        $this->results['order'] = compact('sent', 'already_sent');
    }

    function send_mails($order) {
        include_once('Mail.php');
        include_once('Mail/mime.php');
        
        global $aquarius;
        $smarty = $aquarius->get_smarty_frontend_container();
        
        // get the customized texts from the db
        $confirmation_node = db_Node::get_node("shop_bestaetigung");
        $email_shop = $confirmation_node->get_email();
        $email_client = $order->address->email;
        
        global $lg;
        $smarty->assign('lg', $lg);
        
        // customized texts
        $mail = $confirmation_node->get_content();
        $smarty->assign('mail', $mail);
        $smarty->assign('client_custom1', $confirmation_node->get_text3());
        $smarty->assign('client_custom2', $confirmation_node->get_text4());
        $smarty->assign('client_footer', $confirmation_node->get_text5());
        $smarty->assign('shop_custom1', $confirmation_node->get_text6());
        $smarty->assign('shop_custom2', $confirmation_node->get_text7());
        $smarty->assign('shop_footer', $confirmation_node->get_text8());
        
        $cart_total = $order->cart_total();
  
        $smarty->assign('subtotal', $cart_total['subtotal']);
        $smarty->assign('total', $cart_total['total']);
        $smarty->assign('charges', $cart_total['charges']);
        
        
        
        $smarty->assign('date', date("d.m.y"));
        $smarty->assign('time', date("H:i"));
        $smarty->assign('condition', "condition to do");
        $smarty->assign('address', $order->address);
        $smarty->assign('order', $order);

        $paymethod_node = db_Node::get_node($order->paymethod_id);
        $paymethod_content = $paymethod_node->get_content();
        $paymethod_content->load_fields();
        $smarty->assign('paymethod', $paymethod_content);
        
        // image in template
        $image = 'interface/logo-mail.gif';
        $smarty->assign('image', $image);
        
        // template
        $template_client = 'shop.mail.client.tpl';
        $template_shop = 'shop.mail.shop.tpl';
        
        // client mail
        $html_client = $smarty->fetch($template_client);
        
        $hdrs_client = array(
            'From'    => ('"'.$mail->emailname.'" <'.$email_shop.'>'),
            'Subject' => $mail->emailsubject,
            'Content-Type' => 'multipart/related;charset="UTF-8"'
        );
        
        $mime_client = new Mail_mime("\n");
        $mime_client->setHTMLBody(utf8_decode($html_client));
        $mime_client->addHTMLImage($image);

        $this->send_mail($email_client, $mime_client, $hdrs_client);

         // shop mail
        $html_shop = $smarty->fetch($template_shop);
        
        $hdrs_shop = array(
            'From'    => $email_client,
            'Subject' => $mail->emailsubject,
            'Content-Type' => 'multipart/related;charset="UTF-8"'
        );
            
        $mime_shop = new Mail_mime("\n");
        $mime_shop->setHTMLBody(utf8_decode($html_shop));
        $mime_shop->addHTMLImage($image);

        $this->send_mail($email_shop, $mime_shop, $hdrs_shop);

    }

    function send_mail($to, $mime, $headers) {
        $body = $mime->get();
        $hdrs = $mime->headers($headers);
        $logstr = "mail to $to \n".print_r($hdrs, true)."\n $body";
            
        $mail = Mail::factory('mail');
        $success = $mail->send($to, $hdrs, $body);
        if ($success) {
            Log::info("Sent $logstr");
        } else {
            Log::warn("Failed sending $logstr");
        }
        return $success;
    }

}
