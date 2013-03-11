<?php 
require_once('Mail.php');
require_once('Mail/mime.php');
require_once('Mail/RFC822.php');
require_once('Spreadsheet/Excel/Writer.php');

class action_newsletter extends ModuleAction {
    var $modname = "newsletter";
    var $props = array('class', 'op', 'newsletter_id', 'lg');
    
    function valid($user) {
      return (bool)$user;
    } 
    
    function get_newslettertree($depth) {
        /* get newsletters */
        $newsletters_node = db_Node::get_node(NEWSLETTER_ROOT_NODE) ;       
        $tree = NodeTree::build($newsletters_node, $prefilters = array(), $node_filter = false, $descend_filter = false, $purge_filter = false, $max_depth = $depth);
        return $tree;
    }

    function add_address($new_address, $lg, $subscriptions) {
        $added = array(0,0);
        if (!checkEmail($new_address)) {
            return $added;
        }
        $address = DB_DataObject::factory('newsletter_addresses');
        $address->address=$new_address;
        if(!$address->find()) {
            Log::debug("really new Address");
            $address->language = $lg;
            $address->insert();
            $added[0] += 1;
        } else {
            Log::debug("old address");
            $address->fetch();
        }


        foreach($subscriptions as $sub) {
            $subs = DB_DataObject::factory('newsletter_subscription');
            $subs->address_id = $address->id;
            $subs->newsletter_id = $sub;
            if ($subs->find()) continue;
            
            $subs->active = 1;
            $subs->activation_code = md5($new_address.$sub);
            $subs->insert();
            $added[1] += 1;
        }
        return $added;
    }


    function send($recipients, $edition_node, $lg, $notify_on_send = false) {
        global $aquarius;
        $smarty = $aquarius->get_smarty_frontend_container($lg);
        $edition = new Newsletter_Edition($edition_node, $lg, $smarty);
    
        $transport = $this->module->get_mail_transport();
        
        Log::info("Sending newsletter edition $edition_node ($lg) to ".count($recipients)." recipients. Header: ".print_r($edition->header(), true));
        return $transport->send($recipients, $edition, $notify_on_send);
    }


    function execute() {
        global $aquarius;
        $smarty = false;
        $messages = array();
        $action = false;

        switch($this->op) {
        
        case "listaddresses":
            
            $page = requestvar("page");
            if(empty($page)) {
                $page = 0;
            } else {
                $page--;
            }
                
            $newsletter_addresses = DB_DataObject::factory('newsletter_addresses');
            
            if(isset($_REQUEST['showLangOnly']) and $_REQUEST['showLangOnly'] != "")
            {
            $newsletter_addresses->language = $_REQUEST['showLangOnly'];
            }
            
            if(isset($_REQUEST['contains']) and $_REQUEST['contains'] != "")
            {
            $newsletter_addresses->whereAdd("address LIKE '%" . $_REQUEST['contains'] . "%'");
            }
        
            $newsletter_addresses->orderBy("address");
                
            if($this->newsletter_id != NEWSLETTER_ROOT_NODE) {
                $newsletter_node = DB_DataObject::factory('node');
                $newsletter_node->id = $this->newsletter_id;
        
                $newsletter_node->find(true);
                
                $subscriptions = DB_DataObject::factory('newsletter_subscription');
                $subscriptions->newsletter_id = $this->newsletter_id;
                $newsletter_addresses->joinAdd($subscriptions);
            }
            
            $totalAddressCount = $newsletter_addresses->count();
            $newsletter_addresses->limit($page*ADMIN_NEWSLETTER_ADDRESSES_PER_PAGE,
                ADMIN_NEWSLETTER_ADDRESSES_PER_PAGE);
         
            $rows = $newsletter_addresses->find();
            
            $addresses = array();
            
            while($newsletter_addresses->fetch()) {
                if($this->newsletter_id == NEWSLETTER_ROOT_NODE) {
                    $subscriptions = DB_DataObject::factory('newsletter_subscription');
                    $subscriptions->address_id=$newsletter_addresses->id;
                    $subscriptions->find();
                    $newsletter_addresses->subscriptions = array();
                    while($subscriptions->fetch()) {
                        $newsletter_addresses->subscriptions[] = $subscriptions->newsletter_id;
                    }
                    asort($newsletter_addresses->subscriptions);
                }
                $addresses[] = clone($newsletter_addresses);
            }
            
            $addressCount = count($addresses);
            
            $tree = $this->get_newslettertree(1);
            $flattree = NodeTree::flatten($tree);
            array_shift($flattree);
            
            $addAddressAction = clone $this;
            $addAddressAction->op = 'addaddress';
            
            $delAddressAction = clone $this;
            $delAddressAction->op = 'deladdress';
            
            $smarty = $aquarius->get_smarty_backend_container();
            $smarty->assign('addressesPerPage', ADMIN_NEWSLETTER_ADDRESSES_PER_PAGE);
            $smarty->assign('totalAddressCount', $totalAddressCount);
            $smarty->assign('addresses', $addresses);
            $smarty->assign('available_languages', $this->available_languages());
            $smarty->assign('nodelist', $flattree);
            $smarty->assign('delAddressAction',$delAddressAction);
            $smarty->assign('addAddressAction',$addAddressAction);
            $smarty->assign('newsletterRootNode',NEWSLETTER_ROOT_NODE);
            $smarty->assign('currentPageNr', ++$page);
            
            if($this->newsletter_id != NEWSLETTER_ROOT_NODE) {
                $smarty->assign('newsletter_node', $newsletter_node);
            }
            if($this->newsletter_id == NEWSLETTER_ROOT_NODE) {
                $smarty->tmplname = "newsletter_listaddresses.tpl";
            } else {
                $smarty->tmplname = "newsletter_subscriptions.tpl";
            }
            
            break;

        case "addaddress":
            $new_addresses = array_filter(array_map('trim', split("[ \r\n,;]", requestvar("newAddress"))), 'strlen');
            $subscriptions = requestvar('addSubscriptions', array());
            if ($this->newsletter_id != 0) {
                $subscriptions = array($this->newsletter_id);
            }
            $addresses_ignored = array();
            $added_counts = array(0,0);
            foreach($new_addresses as $new_address) {
                $added = $this->add_address($new_address, requestvar("newLanguage"), $subscriptions);
                $added_counts[0] += $added[0];
                $added_counts[1] += $added[1];
                if ($added[0] == 0) {
                    $addresses_ignored []= $new_address;
                }
            }
            if (count($addresses_ignored) == 0) {
                if ($added[0] == 1) {
                    $messages []= new Translation("newsletter_added_address");
                } else {
                    $messages []= new Translation("newsletter_added_addresses", array($added[0]));
                }
            } elseif (count($addresses_ignored) > 0) {
                $messages []= new Translation("newsletter_added_addresses_ignored", array($added[0], count($addresses_ignored), implode('/', $addresses_ignored)));
            }
            
            if (count($subscriptions) > 0) {
                 $messages []= new Translation("newsletter_added_subscriptions", array($added[1]));
            }
            break;
            
        case "deladdress":
            Log::debug(requestvar('addresses'));
            $addressesToDelete = requestvar('addresses');
            if(!empty($addressesToDelete)) {
                foreach($addressesToDelete as $addrToDelete) {
                    $addr = DB_DataObject::factory('newsletter_subscription');
                    $addr->address_id=$addrToDelete;
                    $addr->delete();
                    $addr = DB_DataObject::factory('newsletter_addresses');
                    $addr->id=$addrToDelete;
                    $addr->delete();
                }
            }
            break;
            
        case "editaddress":
            $editId = requestvar('editId');
            if(!empty($editId)) {
                $addr = DB_DataObject::factory('newsletter_addresses');
                $addr->id = $editId;
                $addr->address = requestvar('editAddress');
                $addr->language = requestvar('editLanguage');
                $addr->requestvar('editAddress');
                $addr->update();
                $subscribedNewsletters = requestvar('editSubscriptions');
                
                $subs = DB_DataObject::factory('newsletter_subscription');
                $subs->address_id = $editId;
                $subs->whereAdd("address_id = $editId");
                $subs->delete(true);
                if(!empty($subscribedNewsletters)) {
                    foreach($subscribedNewsletters as $sub) {
                        $proto = DB_DataObject::factory('newsletter_subscription');
                        $proto->address_id = $editId;
                        $proto->newsletter_id = $sub;
                        Log::debug("sub".$sub);
                        if(requestvar('editActive') == "1" || requestvar('editActive') == "on") {
                            $proto->active = 1;
                        } else {
                            $proto->active = 0;
                        }
                        $proto->activation_code = md5(requestvar('editAddress').$sub);
                        $proto->insert();
                    }
                }
                Log::debug("Active".requestvar('editActive'));
            }
            break;
            
        case "sendnewsletter":
            global $DB;
            $smarty = $aquarius->get_smarty_backend_container();
            $tree = $this->get_newslettertree(2);
            
            $newsletter_root = db_Node::get_node(NEWSLETTER_ROOT_NODE) ;
            $sent_counts = array();
            foreach($newsletter_root->children(array('inactive_self')) as $newsletter_node) {
                foreach($newsletter_node->children(array('inactive_self')) as $edition_node) {
                    $sent    = $this->load_recipients($edition_node, true, false, true);
                    $notsent = $this->load_recipients($edition_node, true, false, false);
                    $sent_counts[$edition_node->id] = compact('sent', 'notsent');
                }
            }
            $smarty->assign('sent_counts', $sent_counts);
            
            $flattree = NodeTree::flatten($tree);
            array_shift($flattree); // Remove root node from tree so it won't be displayed
            $smarty->assign('nodelist', $flattree);
            
            $smarty->tmplname = "newsletter_send.tpl";
            break;


        case 'export':
            $newsletter_node = DB_DataObject::factory('node');
            $newsletter_node->id = NEWSLETTER_ROOT_NODE;
            $newsletter_node->find(true);
            
            $newsletter_subscriptions = array();
      
            if($this->newsletter_id == NEWSLETTER_ROOT_NODE) {
                $newsletters = $newsletter_node->children();
                Log::debug($newsletters);
                
                foreach($newsletters as $newsletter) {
                    $newsletter_addresses = DB_DataObject::factory('newsletter_addresses');
                    $subscriptions = DB_DataObject::factory('newsletter_subscription');
                    $newsletter_addresses->joinAdd($subscriptions);
                    $newsletter_addresses->whereAdd('newsletter_id = "'.$newsletter->id.'"');
            
                    $newsletter_addresses->find();
                    $nla = array();
                    while($newsletter_addresses->fetch()) { 
                        $nla[] = clone($newsletter_addresses);
                    }
                    
                    $newsletter_subscriptions[$newsletter->get_contenttitle()] = $nla;
                   
                }
            } else {
                $newsletter = DB_DataObject::factory('node');
                $newsletter->id = $this->newsletter_id;
                $newsletter->find(true);
                
                $newsletter_addresses = DB_DataObject::factory('newsletter_addresses');
                $subscriptions = DB_DataObject::factory('newsletter_subscription');
                $newsletter_addresses->joinAdd($subscriptions);
                $newsletter_addresses->whereAdd('newsletter_id = "'.$this->newsletter_id.'"');
                $newsletter_addresses->find();
                $nla = array();
                while($newsletter_addresses->fetch()) { 
                    $nla[] = clone($newsletter_addresses);
                }
                $newsletter_subscriptions[$newsletter->get_contenttitle()] = $nla;
                
            }
            
            require_once('Spreadsheet/Excel/Writer.php');

            $smarty = $aquarius->get_smarty_backend_container();
            
            // Creating a workbook
            $workbook = new Spreadsheet_Excel_Writer();
    
            $temp_dir = $aquarius->cache_path('newsletter');
            $valid = $workbook->setTempDir($temp_dir);
            if (!$valid) throw new Exception("Invalid temp dir $temp_dir");

            while(@ob_end_clean());
            
            // sending HTTP headers
            $workbook->send('newsletter_addresses.xls');

            
            foreach($newsletter_subscriptions as $newsletter => $addresses) {
                // Creating a worksheet
                $worksheet =& $workbook->addWorksheet($newsletter);
                
                $worksheet->write(0, 0, $smarty->get_config_vars('newsletter_address'));
                $worksheet->write(0, 1, $smarty->get_config_vars('newsletter_language'));
                $worksheet->write(0, 2, $smarty->get_config_vars('newsletter_subscription_date'));
                $worksheet->write(0, 3, $smarty->get_config_vars('newsletter_subscription_active'));
                
                $i = 1;
                foreach($addresses as $addr) {
                
                    // The actual data
                    $worksheet->write($i, 0, $addr->address);
                    $worksheet->write($i, 1, $addr->language);
                    $worksheet->write($i, 2, $addr->subscription_date);
                    $worksheet->write($i, 3, $addr->active);
                    $i++;
                }
            }
            $workbook->close();
            exit();

            
        case 'cleanuplist':
            $subscriptions_to_cleanup = $this->get_subscriptions_to_cleanup();
            Log::debug($subscriptions_to_cleanup);
            $smarty = $aquarius->get_smarty_backend_container();
            $smarty->assign('subscriptions_to_cleanup', $subscriptions_to_cleanup);
            $smarty->assign('cleanup', true);
            $smarty->tmplname = "newsletter_cleanup.tpl";
            break;
            
        case 'cleanup':
            $subscriptions_to_cleanup = $this->get_subscriptions_to_cleanup();
            foreach($subscriptions_to_cleanup as $subscription) {
                
                $s = DB_DataObject::factory('newsletter_subscription');
                $s->newsletter_id=$subscription->newsletter_id;
                $s->delete();
            }
            Log::debug($subscriptions_to_cleanup);
            $smarty = $aquarius->get_smarty_backend_container();
            $smarty->assign('subscriptions_to_cleanup', $subscriptions_to_cleanup);
            $smarty->assign('cleanup', true);
            $smarty->tmplname = "newsletter_cleanup.tpl";
            break;

        default:
            throw new Exception("Operation unknown: '$this->op'");
        }
        return compact('messages', 'smarty','action');
    }

    
    function get_subscriptions_to_cleanup() {
        $newsletter_node = DB_DataObject::factory('node');
        $newsletter_node->id = NEWSLETTER_ROOT_NODE;
        $newsletter_node->find(true);
        $newsletters = $newsletter_node->children();
        
        $subscriptions_to_cleanup = array();
        foreach($newsletters as $newsletter) {
            $subscriptions = DB_DataObject::factory('newsletter_subscription');
            $newsletter_addresses = DB_DataObject::factory('newsletter_addresses');
            $newsletter_addresses->joinAdd($subscriptions);
            $newsletter_addresses->whereAdd('newsletter_id="'.$newsletter->id.'"');
            $newsletter_addresses->find();
            while($newsletter_addresses->fetch()) {
                $timestamparray = strptime($newsletter_addresses->subscription_date, "%Y-%m-%d %H:%M:%S");
                $timestampunix = mktime(0,0,0,$timestamparray['tm_mon']+1,$timestamparray['tm_mday'], $timestamparray['tm_year']);
                Log::debug("old:".$timestampunix);
                Log::debug("actual:".time());
                if($timestampunix + NEWSLETTER_CLEAN_DELTA <= time() && $newsletter_addresses->active == 0) {
                    
                    $subscriptions_to_cleanup[] = clone($newsletter_addresses);
                }
            }
        }
        Log::debug($subscriptions_to_cleanup);
        return $subscriptions_to_cleanup;
    }


    /** Load list of recipients for given edition */
    function load_recipients($edition_node, $count_only, $only_lg = null, $sent = null) {
        global $DB;

        $wheres = array();
        $wheres []= "newsletter_subscription.active = 1";
        $wheres []= "newsletter_subscription.newsletter_id = ".$edition_node->get_parent()->id;
        if ($only_lg) $wheres []= 'newsletter_addresses.language = "'.mysql_real_escape_string($only_lg).'"';
        
        $joins = array();
        $joins []= "JOIN newsletter_subscription ON newsletter_subscription.address_id = newsletter_addresses.id";

        if ($sent !== null) {
            $joins []= "
                LEFT JOIN newsletter_sent 
                    ON newsletter_sent.address_id = newsletter_addresses.id
                    AND newsletter_sent.edition_id = $edition_node->id
            ";
            if ($sent) {
                $wheres []= 'newsletter_sent.sent = 1';
            } else {
                $wheres []= ('newsletter_sent.id IS NULL');
            }
        }

        $query_tail =  "FROM newsletter_addresses ".join("\n", $joins)." WHERE ".join(" AND ", $wheres);

        if ($count_only) {
            return $DB->singlequery("SELECT COUNT(newsletter_addresses.id) $query_tail");
        } else {
            $recipients = array();
            $results = $DB->queryhash("SELECT newsletter_addresses.id, newsletter_addresses.address $query_tail");
            foreach($results as $result) {
                $recipients[$result['id']] = $result['address'];
            }
            return $recipients;
        }
    }
    
    function check_addresses($addresses) {
        $valid = array();
        $invalid = array();
        foreach($addresses as $address) {
            $address_parts = Mail_RFC822::isValidInetAddress($address);
            if ($address_parts == false) {
                $invalid []= $address;
            } else {
                $valid []= $address_parts[0].'@'.$address_parts[1];
            }
        }
        return compact('valid', 'invalid');
    }
    
    /** Load list of languages where the given edition has content  */
    function available_languages($edition_node = false) {
        $language = DB_DataObject::factory('languages');
        $language->find();
        $available_languages = array();
        while($language->fetch()) {
            if (!$edition_node || $edition_node->get_content($language->lg)) {
                $available_languages[$language->lg] = array(
                    'lg' => $language->lg,
                    'name' => $language->name
                );
            }
        }
        return $available_languages;
    }
}


class Action_Newsletter_Preview extends Action_Newsletter implements SideAction {
    var $props = array('class', 'op', 'edition_id', 'lg');

    function get_title() {
        return new Translation('newsletter_preview');
    }

    function get_icon() {
        return 'buttons/theeye.gif';
    }

    function process($aquarius, $params) {
        $edition_node = db_Node::get_node($this->edition_id);

        $smarty = $aquarius->get_smarty_frontend_container($this->lg);
        $edition = new Newsletter_Edition($edition_node, $this->lg, $smarty);
        echo $edition->body();
    }
}

class Action_Newsletter_Presend extends Action_Newsletter implements DisplayAction {
    var $props = array('class', 'op', 'edition_id');

    function get_title() {
        return str(new Translation('newsletter_send'));
    }

    function process($aquarius, $params, $smarty, $result) {
        global $DB;
        
        $edition_node = db_Node::get_node($this->edition_id);
        $languages = $this->available_languages();
        $available_languages = $this->available_languages($edition_node);
        $notsent_total = 0;
        $sent_counts = array();
        foreach($languages as &$language) {
            $language['available'] = array_key_exists($language['lg'], $available_languages);
            $language['sent']    = $this->load_recipients($edition_node, true, $language['lg'], true);
            $language['notsent'] = $this->load_recipients($edition_node, true, $language['lg'], false);
            $notsent_total += $language['notsent'];
        }
        $smarty->assign('available_languages', $available_languages);
        $smarty->assign('languages', $languages);

        $actions = array();
        if ($notsent_total > 0) $actions []= Action::make('newsletter', 'send_confirm', $this->edition_id);
        $actions []= Action::make('cancel');

        $smarty->assign('actions', $actions);
        $smarty->assign('test_action', Action::make('newsletter', 'send_test', $this->edition_id));
        $smarty->assign('newsletter_name', $edition_node->get_parent()->get_name());
        $smarty->assign('a_languages', $this->available_languages($edition_node));
        $smarty->assign('newsletter_id', $this->edition_id);
        $smarty->assign('edition_title', $edition_node->get_title());
        $result->use_template("newsletter_presend.tpl");
    }
}

class Action_Newsletter_Send_Confirm extends Action_Newsletter implements DisplayAction {
    var $props = array('class', 'op', 'edition_id');

    function get_title() {
        return str(new Translation('newsletter_send'));
    }

    function process($aquarius, $params, $smarty, $result) {
        $edition_node = db_Node::get_node($this->edition_id);
        $lgs = $params['c_languages'];

        $address_count = 0;
        foreach($lgs as $lg) {
            $address_count += $this->load_recipients($edition_node, true, $lg, false);
        }
        $smarty->assign('message', new Translation('newsletter_send_to_n_recipients', array($edition_node->get_title(), $address_count)));

        $actions = array();
        if ($address_count > 0) {
            $actions []= Action::make('newsletter', 'send_it', $this->edition_id, join(',', $lgs));
        }
        $actions []= Action::make('cancel');
        $smarty->assign('actions', $actions);
        
        $result->use_template('select.tpl');
    }
}

class Action_Newsletter_Send_It extends Action_Newsletter implements ChangeAction {
    var $props = array('class', 'op', 'edition_id', 'lgs');

    function get_title() {
        return new Translation('newsletter_send');
    }

    function process($aquarius, $post, $result) {
        $edition_node = db_Node::get_node($this->edition_id);
        
        $selected_languages = split(',', $this->lgs);
        $message = new AdminMessage('info');
        $total_count = 0;
        foreach($this->available_languages($edition_node) as $language) {
            $lg = $language['lg'];
            if (in_array($lg, $selected_languages)) {
                // Load recipients where the message has not been sent yet
                $recipients = $this->load_recipients($edition_node, false, $lg, false);


                $checked_recipients = $this->check_addresses($recipients);
                if (count($checked_recipients['invalid']) > 0) {
                    $result->add_message(AdminMessage::with_line('warn', 'newsletter_invalid_addresses', join(', ', $checked_recipients['invalid'])));
                }

                // Prepare address_id lookup table for the mark_as_sent callback
                $this->_address_ids = array_flip($recipients);
                $sent_callback = array($this, 'mark_as_sent');

                // Send to recipients
                $count = $this->send($checked_recipients['valid'], $edition_node, $lg, $sent_callback);
                $total_count += $count;
                $message->add_line('newsletter_sent_n_lg', $count, $language['name']);
            }
        }
        $message->add_line('newsletter_sent_to_n', $total_count);
        $result->add_message($message);
    }
    
    function mark_as_sent($addresses) {
        global $DB;
        foreach($addresses as $address) {
            $address_id = $this->_address_ids[$address];
            if (!$address_id) throw new Exception("Address $address not in id lookup table");
            $DB->query('INSERT INTO newsletter_sent SET edition_id = '.$this->edition_id.', address_id = '.$address_id.' , sent = 1');
        }
    }
}

class Action_Newsletter_Send_Test extends Action_Newsletter implements ChangeAction {
    var $props = array('class', 'op', 'edition_id');
    function get_title() {
        return new Translation('newsletter_send_test');
    }

    function process($aquarius, $post, $result) {
        // Read comma separated list of mail addresses, discarding blanks
        $recipients = array_filter(array_map('trim', split(",",$post['test_mail'])));

        $checked_recipients = $this->check_addresses($recipients);
        if (count($checked_recipients['invalid']) > 0) {
            $result->add_message(AdminMessage::with_line('warn', 'newsletter_invalid_addresses', join(', ', $checked_recipients['invalid'])));
        }
        $selected_lgs = $post['c_languages'];
        
        if (count($checked_recipients['valid']) > 0) {
            $edition_node = db_Node::get_node($this->edition_id);
            $message = new AdminMessage('info');
            foreach($this->available_languages($edition_node) as $language) {
                if (in_array($language['lg'], $selected_lgs)) {
                    $count = $this->send($checked_recipients['valid'], $edition_node, $language['lg']);
                    $message->add_line('newsletter_sent_n_lg', $count, $language['name']);
                }
            }
            if ($count > 0) $result->add_message($message);
        }
    }
}
