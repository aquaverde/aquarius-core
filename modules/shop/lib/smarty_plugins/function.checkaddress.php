<?php 
/** @package Aquarius.frontend.shop
  */

/** 
  * smarty function which checks following things:
  * - if the user is logged in or tries to log in
  * - if the user tries signs in or tries to update its adress
  * - if the user wants to pay the order.
  */
function smarty_function_checkaddress($params, &$smarty) {
    Log::debug($_REQUEST);

    //needed by the login template
    $smarty->assign("session_id",session_id());

    //FOR TESTING
    if (requestvar("logout")) {
        $_SESSION["shop"]["user_id"] = "";
    }

    //check if user is logged in or tries to login
    $loggedin = false;
    if (get(requestvar("shop"),"login",false)) {
        //the user seems to ask for login, let's check its credentials
        $dbusers = DB_DataObject::factory('shop_fe_users');
        $user = $dbusers->check_login();
        Log::debug($user);
        if (is_array($user)) {
            $loggedin = true;
            $_SESSION["shop"]["user_id"] = $user["id"];
            $_SESSION["shop"]["user_name"] = $user["name"];
        } else {
            Log::debug("Wrong username/password");
        }
    } elseif (intval($_SESSION["shop"]["user_id"]) > 0) {
        $loggedin = true;
    } 


    $smarty->assign("loggedin",$loggedin);
    //if (!$loggedin) return;

    //check if the user tries to update the address, or wants to sign in
    //this method should be checked with captcha images or sth else, such
    //that nobody can create accounts automatically
    $addressgiven = false;
    $addresses = get(requestvar("shop"),"change_address",false);
    if ($addresses) {
        $address = get(requestvar("shop"),"address",false);
        print_r($address); //die();
        $addressgiven = true;
        //overwrite entry in db with given address
        $dbuser = DB_DataObject::factory('shop_fe_users');
        $user_id = $dbuser->update_address($address,$_SESSION["shop"]["user_id"]);
        $_SESSION["shop"]["user_id"] = $user_id;
        print_r($user_id); die();
        //user is logged in after creating an account
        $loggedin = true;
    }
    
    $smarty->assign("addressgiven",$addressgiven);
    
    //load address if logged in
    if ($loggedin) {
        $dbusers = DB_DataObject::factory('shop_fe_users');
        $baddr = $dbusers->get_addresses($_SESSION["shop"]["user_id"],"billing");
        $saddr = $dbusers->get_addresses($_SESSION["shop"]["user_id"],"shipping");

        $smarty->assign("billaddress",$baddr);
        $smarty->assign("shipaddress",$saddr);
    }


    //check if user wants to pay
    $payrequested = false;
    if (get(requestvar("shop"),"pay",false)) {
        $payrequested = true;
    }
    
    $smarty->assign("payrequested",$payrequested);
    
    return;
}
