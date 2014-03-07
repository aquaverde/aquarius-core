<?php 

class Shop_Frontend {
    var $module_core;

    /** assoc array with results of operations such as add_to_cart */
    var $results = array();

    function __construct($core) {
        $this->module_core = $core;
    }

    /** Get an id that should be unique for an attribute
      * Tries to use the name of the node but uses it's id if there is no name. */
    function attribute_idstr($attribute) {
        if (strlen($attribute->name) > 0) return $attribute->name;
        return 'attribute_'.$attribute->id;
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
            $attribute_settings[$this->attribute_idstr($attribute)] = $attribute_values;
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
      *   operation: name of the opeartion
      * False is returned if there are no results, likely because the opeartion was not executed */
    function get_result($params) {
        return get($this->results, get($params, 'operation'), false);
    }
    
    /** Get shopping cart from session */
    function get_order() {
        $order = get($_SESSION, 'shop_order');

        if (!$order instanceof Shop_Transient_Order) $this->initialize_order() ;
        return $_SESSION['shop_order'] ; 
    }
    
    function initialize_order() {
        $order = new Shop_Transient_Order();

        $charges_node = db_Node::get_node('shop_charges');
        $charges = $charges_node->children(array('inactive'));

        foreach($charges as $charge) {
            $limit = $charge->get_surcharge_limit();
            if (strlen($limit)) {
                $limit = Shop::parse_currency($limit);
            } else {
                $limit = false;
            }
            $order->add_charge($charge->get_title(), $charge->get_surcharge(), 0, $limit, $charge->get_message());
        }
        $_SESSION['shop_order'] = $order;
    }

    function process_requests($smarty) {
        if (isset($_REQUEST['add_to_cart'])) $this->add_to_cart();
        if (isset($_REQUEST['update_cart'])) $this->update_cart();
        if (isset($_REQUEST['shop_select_paymethod'])) $this->select_paymethod();
        if (isset($_REQUEST['shop_order'])) $this->order($this->get_frozen_order($_REQUEST));
        if (isset($_REQUEST['txn_id']) and isset($_REQUEST['invoice'])) $this->process_paypal_ipn(clean_magic($_REQUEST));
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
            $property_id = requestvar($this->attribute_idstr($attribute));

            // Add the property if it's active for this product
            if ($property_id && isset($properties[$property_id])) {
                $selected_properties[$attribute->id] = $property_id;
            }
        }

        $additional_properties = requestvar('property');
        if (!is_array($additional_properties)) {
            $additional_properties = array();
        }

        $order_item = Shop_order_item::create($product, $count, $attributes, $selected_properties, $additional_properties);

        // Put product in cart, only if count > 0
        if ($order_item->count > 0) {
            // If the same item exists already, update that item's count instead of adding a separate item
            $order = $this->get_order();
            $merged = false;
            foreach ($order->items as $other_item) {
                if ($order_item->same($other_item)) {
                    $other_item->count += $order_item->count;
                    $merged = true;
                    break;
                }
            }
            if (!$merged) {
                $order->items[] = $order_item;
            }
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


    function select_deliverymethod() {
        $_SESSION['delivery_method'] = intval(requestvar('delivery_method'));
    }

    function get_delivery_methods() {
        $parent = db_Node::get_node('shop_delivery_methods');        
        if (!$parent) throw new Exception("Missing shop_delivery_methods node");
        $delivery_method_nodes = $parent->children(array('inactive'));        
        $delivery_methods = array();
        $selected_delivery_method = get($_SESSION, 'delivery_method', 0);
                
        foreach($delivery_method_nodes as $delivery_method_node) {
            $content = $delivery_method_node->get_content();
            $content->load_fields();            
            if ($selected_delivery_method > 0) $content->selected = $delivery_method_node->id == $selected_delivery_method;
            else $content->selected = $content->enabled;
            $delivery_methods[$delivery_method_node->id] = $content;
        }
                
        return $delivery_methods;
    }

    function get_deliverymethod() {
        $deliverymethod = $_SESSION['delivery_method'];
        $available_deliverymethods = $this->get_delivery_methods();
        $deliverynode = $available_delidery_methods[$deliverymethod];
        if (!$deliverymethod) throw new Exception("Invalid deliverymethod $deliverymethod");
        return $deliverynode;
    }
    
    function select_paymethod() {
        $_SESSION['pay_method'] = intval(requestvar('pay_method'));
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

    function get_paymethod() {
        $paymethod = $_SESSION['pay_method'];
        $available_methods = $this->get_methods();
        $paynode = $available_methods[$paymethod];
        if (!$paynode) throw new Exception("Invalid paymethod $paymethod");
        return $paynode;
    }

    /** Places the current order information under a unique id in the session
      * During checkout it is necessary to keep order information separate so that it will not be modified if the user changes something in the main shopping cart.
      * @return the frozen order (must keep its id to access the order again) */
    function freeze_order() {
        $user = db_Fe_users::authenticated();
        if ($user) {
            $address = $this->get_address();
            $pay_node = $this->get_paymethod();

            $order = $this->get_order();
            $final_order = $order->make_finalized($user, $address, $pay_node);

            // The idea was to finalize orders and store them in the session. They were made persistent as soon as they were confirmed. But for offsite payment such as PayPal this is too brittle.
            $persistent_order = $final_order->make_persistent('temporary'); 

            return $persistent_order;
        }
        return false;
    }

    /** Confirm order */
    function order($order) {
        $sent = false;
        $error = false;

        if ($order->status == 'temporary') {
            $order->status = 'pending';
            $order->update();
            $order->set_sequence_nr();

            $this->send_mails($order);
            $sent = true;
            $this->initialize_order();
        }
        
        $this->results['order'] = compact('sent', 'error', 'order');
        return $error;
    }

    function get_address() {
        global $aquarius;
        return $aquarius->modules['fe_user_management']->get_address();
    }

    /** Load order data from DB
      * @param order_id
      * @return Shop_order object or false if ther is no order for this ID */
    function load_order($order_id) {
        $order = new Shop_order();
        if ($order->get($order_id)) {
            return $order;
        } else {
            return false;
        }
    }

    /** Read order id from request array and load order
      * Throws error if it fials loading.
      * DEPRECATED method doing too much at once
      */
    function get_frozen_order($request) {
        $id = get($request, 'order_id');
        $order = $this->load_order($id);
        if (!$order) throw new Exception("No order for order_id '$id'");
        return $order;
    }

    function send_mails($order) {
        require_once('Mail.php');
        require_once('Mail/mime.php');
        
        global $aquarius;
        global $lg;
        $smarty = $aquarius->get_smarty_frontend_container($lg);
        $smarty->caching = false;
        
        // get the customized texts from the db
        $confirmation_node = db_Node::get_node("shop_bestaetigung");
        $email_shop = $confirmation_node->get_email();

        $address = $order->get_address();
        $email_client = $address->email;
        
        global $lg;
        $smarty->assign('lg', $lg);
        $smarty->assign('charset', 'iso-8859-1');
        
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
        $smarty->assign('cart_total', $cart_total);
        $smarty->assign('subtotal', $cart_total['subtotal']);
        $smarty->assign('total', $cart_total['total']);
        $smarty->assign('charges', $cart_total['charges']);
        


        $smarty->assign('date', date("d.m.y"));
        $smarty->assign('time', date("H:i"));
        $smarty->assign('address', $address);
        $smarty->assign('order', $order);

        $paymethod_node = db_Node::get_node($order->paymethod_id);
        $paymethod_content = $paymethod_node->get_content();
        $paymethod_content->load_fields();
        $smarty->assign('paymethod', $paymethod_content);
        
        // image in template
        $image = PROJECT_URL.'/interface/logo-mail.gif';
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
        //$mime_client->addHTMLImage($image);

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
        $logstr = "mail to $to \n".print_r($hdrs, true)."\n".print_r($body, true);
            
        $mail = Mail::factory('mail');
        $success = $mail->send($to, $hdrs, $body);
        if ($success) {
            Log::info("Sent $logstr");
        } else {
            Log::warn("Failed sending $logstr");
        }
        return $success;
    }

    /** Process Paypal Instant Payment Notification messages
      * This verifies notififications with Paypal and confirms order status */
    function process_paypal_ipn($post) {
        // Based on example code from Paypal
        // https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNImplementation

        // Validation request setup
        // Include all posted fields
        $req = 'cmd=_notify-validate';
        foreach ($post as $key => $value) {
            $req .= "&$key=".urlencode($value);
        }

        // See whether this is a 'sandbox' IPN
        $testing = get($post, 'test_ipn') == '1';

        // Load connection parameters
        $connection_params = $this->get_paypal_param($testing);

        // Header setup
        $header  = "POST ".$connection_params['path']." HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n";

        // Now let's see
        $error = false;
        $response_lines = array();
        try {
            $verify_url = 'ssl://'.$connection_params['host'];
            Log::debug("Verifying with PAYPAL, connecting to ".$verify_url);
            $paypal_connection = fsockopen ('ssl://'.$connection_params['host'], 443, $errno, $errstr, 30);
            if (!$paypal_connection) throw new Exception("Unable to open verification connection. Error: $errno ($errstr))");

            $request = $header."\r\n".$req;
            Log::debug("Sending verification request: \n$request");
            fputs($paypal_connection, $request);

            $verified = false;
            while(!feof($paypal_connection)) {
                $response_line = fgets($paypal_connection);
                if (trim($response_line) == "VERIFIED") {
                    $verified = true;
                }
                $response_lines []= $response_line;
            }
            if (!$verified) throw new Exception("Verification by Paypal failed: ".implode('', $response_lines));
        } catch (Exception $e) {
            process_exception($e);
        }
        fclose($paypal_connection);

        // Looks like the payment went through
        // Make sure we're not being tricked here

        // Receiving account must be correct
        $receiving_account = $post['receiver_email'];
        $our_account = $connection_params['account'];
        if ($receiving_account != $our_account) {
            $error = "Receiving account ('$receiving_account') different from our account ('$our_account')";
        }

        $order = false;
        if (!$error) {
            // Field invoice should contain order id
            $order_id = $post['invoice'];
            $order = $this->load_order($order_id);
            if (!$order) {
                $error = "Invalid order id received: '$order_id'";
            }
        }

        if (!$error) {
            // Currency must match configured currency
            $transaction_currency = $post['mc_currency'];
            $our_currency  = $this->module_core->conf('paypal/currency');
            if ($transaction_currency != $our_currency) {
                $error = "Transaction currency ('$transaction_currency') not in our currency ('$our_currency')";
            }
        }

        if (!$error) {
            // Make sure we got our money
            $transaction_total = Shop::parse_currency($post['mc_gross']);
            if ($transaction_total < $order->total_price) {
                $error = "Transaction gross total ($transaction_total) less than order total ($order->total_price)";
            }
        }

        if (!$error) {
            $payment_status = $post['payment_status'];
            if($payment_status == "Completed") {
                $order->paid = true;
                $error = $this->order($order);
            }
            else if(($payment_status == "In-Progress" or $payment_status == "Pending" or $payment_status == "Processed")) {
                $error = $this->order($order);
            }
            else {
                $error = "Unknown payment_status '$payment_status'";
            }
        }

        // Log result
        $for_order = $order ? " for order ".$order->id : "";
        $prefix = "Paypal IPN processing transaction {$post['txn_id']}$for_order: ";
        if ($error) {
            Log::fail($prefix.$error);
        } else {
            Log::info($prefix."success");
        }

        // Processing ends here
        while(@ob_end_clean());
        header("HTTP/1.1 202 Thanks"); // Always be nice to fellow computer systems; my mother thaught me
        exit();
    }

     /** get the paypal parameters
       * @param testing force returning sandbox parameters if this is true; optional
       * @return configuration variables shop/paypal
       * If there is a logged in user with an id equal to config shop/paypal/test_user, then parameters from shop/paypal_test are returned instead to facilitate testing with sandbox.paypal.com. */
    function get_paypal_param($testing=false) {
        $user = db_Fe_users::authenticated();
        $conf_path = 'paypal/connection';
        if($testing || $user && $user->id == $this->module_core->conf('paypal/test_user')) {
            Log::debug("Using Paypal sandbox parameters, this is not for real");
            $conf_path = 'paypal/connection_sandbox';
        }
        $config = $this->module_core->conf($conf_path);
        Log::debug("Using Paypal parameters ".print_r($config, true));
        return $config;
    }
    
}
