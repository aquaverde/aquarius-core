<?
/** @package Aquarius.frontend
  */

/** Subscribes an email to a newsletter.
  * <pre>
  * Params:
  *   newsletter: the newsletter id to subscribe to
  *   lg: Search for content in that language (uses current language by default)
  *   
  * </pre>
  *
  * <pre>
  * Assigns results to $result variable:
  * </pre>
  *
  */
  
require_once('lib/newslettermail.php');

require_once('pear/Validate.php');

function get_url($show_port = false)
{
    if($_SERVER['HTTPS'])
    {
        $my_url = 'https://';
    }
    else
    {
        $my_url = 'http://';
    }

    $my_url .= $_SERVER['HTTP_HOST'];

    if($show_port)
    {
        $my_url .= ':' . $_SERVER['SERVER_PORT'];
    }

    $my_url .= $_SERVER['REQUEST_URI']."?";

//     if($_SERVER['QUERY_STRING'] != null)
//     {   
//         $my_url .= '?' . $_SERVER['QUERY_STRING'];
//     }
    return $my_url;
}


function smarty_function_subscribe($params, &$smarty) 
{
    global $lg;
    
    /* Parameters that may be passed on function invocation or in HTTP request */
    $email = trim(get($params, 'email', requestvar('email')));
    $newsletter = trim(get($params, 'newsletter', requestvar('newsletter')));
    $lg = str(get($params, 'lg', $lg));
    $userCode = trim(requestvar('activationCode'));
    $subscribe = trim(requestvar('subscribe'));
    $activate = trim(requestvar('activate'));
    $unsubscribe = trim(requestvar('unsubscribe'));
    $unsubscribeConfirm = trim(requestvar('unsubscribeConfirm'));
    
    $subscribing = !empty($subscribe) && !empty($email) && !empty($newsletter);
    $activating = !empty($activate) && !empty($userCode);
    $unsubscribing = !empty($unsubscribe) && !empty($newsletter) && empty($userCode);
    $unsubscribingConfirming = !empty($unsubscribeConfirm) &&  !empty($userCode);
    
    if(!$subscribing && !$activating && !$unsubscribing && !$unsubscribe && !$unsubscribingConfirming) {
        $displaySubscribe = 1;
        $smarty->assign('result', compact('displaySubscribe'));
        return '';
    }
    
    if(!$subscribing && !$activating && !$unsubscribing && $unsubscribe && !$unsubscribingConfirming) {
        $displayUnsubscribe = 1;
        $smarty->assign('result', compact('displayUnsubscribe'));
        return '';
    }
    
    $alreadySubscribed = 0;
    $subscriptionOK = 0;
    $activationOK = 0;
    $unsubscriptionOK = 0;
    $unsubscriptionNotSubscribed = 0;
    $unsubscriptionConfirmOK = 0;
  
    if($subscribing) {
        Log::debug("subscribing");
        if(!checkEmail($email, false)) {
            $subscribeEmailInvalid = 1;
            $smarty->assign('result', compact('subscribing','subscribeEmailInvalid'));
            return '';
        }
        $address = DB_DataObject::factory('newsletter_addresses');
        $address->address=$email;
        $address->language = $lg;
        if($address->find()) {
            Log::debug("already have address");
            $address->fetch();
        } else {
            Log::debug("new address");
            $address->insert();
        }
        
        $subscriptions = DB_DataObject::factory('newsletter_subscription');
        $subscriptions->newsletter_id = $newsletter;
        $subscriptions->address_id = $address->id;
        $subscriptions->activation_code = md5($email.$newsletter);
        if(!$subscriptions->find()) {
            Log::debug("really new newsletter");
            $subscriptions->insert();
            $address->insert();
            
            $newsletter_node = DB_DataObject::factory('node');
            $newsletter_node->id = $newsletter;
            $newsletter_node->find();
            
            $newsletter_content = $newsletter_node->get_content();
            $newsletter_content->load_fields();
            
            $fromAddress = $newsletter_content->from.
                        " <".$newsletter_content->from_email.">";
            $url = get_url();
            $activationLink = '<a href="'
            .$url.'&activate=1&activationCode='
            .$subscriptions->activation_code.'">'
                .$url.'&activate=1&activationCode='
                .$subscriptions->activation_code
            .'</a>';
            $body = $newsletter_content->activation_email.
            "\n".$activationLink;
            newslettermail($fromAddress, $email, "", $newsletter_content->activation_subject, $body);
            
            $alreadySubscribed = 0;
            $subscriptionOK = 1;
        } else {
            $address->delete();
            $alreadySubscribed = 1;
        }
    } elseif($activating) {
        Log::debug("activating".$activating);
        
        $subscription = DB_DataObject::factory('newsletter_subscription');
        $subscription->activation_code = $userCode;
        if($subscription->find()) {
            $query = 'UPDATE '.$subscription->__table.' 
                    SET active = 1
                    WHERE activation_code = "'.$userCode.'"';
            $subscription->query($query);
            
            $subscription = DB_DataObject::factory('newsletter_subscription');
            $subscription->activation_code = $userCode;
            $subscription->find(true);
            
            $newsletter_node = DB_DataObject::factory('node');
            $newsletter_node->id = $subscription->newsletter_id;
            $newsletter_node->find(true);
            
            $newsletter_content = $newsletter_node->get_content();
            $newsletter_content->load_fields();
            
            $fromAddress = $newsletter_content->from.
                        " <".$newsletter_content->from_email.">";
                        
            $address = DB_DataObject::factory('newsletter_addresses');
            $address->id=$subscription->address_id;
            $address->find(true);
            
            $toAddress = $address->address;
            $body = $newsletter_content->subscription_email;
            
            newslettermail($fromAddress, $toAddress, "", $newsletter_content->subscription_subject, $body);
        
            $activationOK = 1;
        } else {
            $activationOK = 0;
        }
    } 
    elseif($unsubscribing) 
    {
        Log::debug("unsubscribing");
        DB_DataObject::debugLevel(0);
        $address = DB_DataObject::factory('newsletter_addresses');
        $address->address=$email;
        if($address->find()) {
            Log::debug("ok");
            $address->fetch();
            
            $subscriptions = DB_DataObject::factory('newsletter_subscription');
            $subscriptions->newsletter_id = $newsletter;
            $subscriptions->address_id = $address->id;
            if($subscriptions->find(true)) {
                $newsletter_node = DB_DataObject::factory('node');
                $newsletter_node->id = $newsletter;
                $newsletter_node->find(true);
                
                $newsletter_content = $newsletter_node->get_content();
                $newsletter_content->load_fields();
                
                
                $fromAddress = $newsletter_content->from.
                            " <".$newsletter_content->from_email.">";
                $url = get_url();
                $confimLink = '<a href="'
                .$url.'&unsubscribeConfirm=1&activationCode='
                .$subscriptions->activation_code.'">'
                    .$url.'&unsubscribeConfirm=1&activationCode='
                    .$subscriptions->activation_code
                .'</a>';
                $body = $newsletter_content->unsubscription_confirm_email.
                "\n".$confimLink;
                newslettermail($fromAddress, $email, "", $newsletter_content->unsubscription_confirm_subject, $body);
            
                $unsubscribeOK = 1;
            } else {
                Log::debug("not subscribed");
                $unsubscriptionNotSubscribed = 1;
            }
        
        } else {
            Log::debug("not subscribed");
            $unsubscriptionNotSubscribed = 1;
        }
        DB_DataObject::debugLevel(0);
        
    } else if($unsubscribingConfirming) {
        $subscriptions = DB_DataObject::factory('newsletter_subscription');
        $subscriptions->activation_code = $userCode;
        $subscriptions->delete();
        $unsubscriptionConfirmOK = 1;
    }
    $smarty->assign('result', 
    	compact('subscribing',
    			'subscriptionOK', 
    			'alreadySubscribed', 
    			'activating',
    			'activationOK', 
    			'unsubscribing', 
    			'unsubscribeOK', 
    			'unsubscriptionNotSubscribed',
    			'unsubscribingConfirming', 
    			'unsubscriptionConfirmOK'));
    return '';
}
?>