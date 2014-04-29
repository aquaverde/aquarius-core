<?php

/**
* Path to attribute pictures
*/
define("SHOP_PICTURE_FOLDER","pictures/shop/products/");


/**
* To compare with the form-title of the node we have; to know "where we are".
*/
define('SHOP_ATTRIBUTE_FORM_ID', 133);
define('SHOP_ATTRIBUTE_SELECTION_FIELD_NAME', 'shop_attribute_selection');
define("CATEGORY_FORM_NAME","shop_category");
define("SUBCATEGORY_FORM_NAME","shop_subcategory");
define("PRODUCT_FORM_NAME","shop_product");
define("PAYMENT_NODE_FORM","shop_paymethod");

// Discount settings
define('SHOP_DISCOUNT_GROUP_ID', 4);
define('SHOP_DISCOUNT_PERCENT', 10);

// Minimum amount for order, in cents
define('SHOP_ORDER_MINIMUM', 1000);

// The default timespan to show in the order overview
define('SHOP_ORDER_LIST_DEFAULT_TIMESPAN', 60*60*24*31);

define('SHOP_DELETE_TEMPORARY_ORDERS_DELAY', 60*60*24*31);

// PayPal
$config['shop']['paypal']['test_user'] = 1;
$config['shop']['paypal']['currency'] = 'CHF';

$config['shop']['paypal']['connection'] = array(
    'host'    => 'www.paypal.com',
    'path'    => '/cgi-bin/webscr',
    'account' => "",
    'token'   => ""
);
$config['shop']['paypal']['connection_sandbox'] = array(
    'host'    => "www.sandbox.paypal.com",
    'path'    => "/cgi-bin/webscr",
    'account' => "shop_1195465615_biz@aquaverde.ch",
    'token'   => "KtwO_7dPb_lzgxSGaTiE7X_UoFjBlyKNuytXfAfJy-P6DgQj3CF0i1ANMWy"
);


