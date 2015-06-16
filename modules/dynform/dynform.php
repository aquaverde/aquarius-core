<?php
class Dynform extends Module {
    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend', 'contentedit_addon', 'node_copy', 'node_delete') ;
    var $short = "dynform" ;
    var $name  = "Dynform Module" ;

    function menu_init($menu, $lg) 
    {
        $menu->add_entry(
            'menu_modules',
            10,
            new Menu('dynform',false
        )) ;

        $menu->add_entry(
            'dynform',
            20,
            new Menu('export_data', Action::make('dynform_menu', 'data', $lg)
        )) ;
        
        if (DYNFORM_SHOW_SETTINGS) {
            $menu->add_entry(
                'dynform',
                30,
                new Menu('settings', Action::make('dynform_menu', 'settings', $lg)
            )) ;
        }
    }
    
    function setup() {
        /* If dynform template dir does not exist, create it */
        global $aquarius; // It should be set as module property $this->aquarius but it isn't during setup!?
        $template_dir = $aquarius->install_path.'templates/';

        $dynform_template_dir = $template_dir.'dynform/';
        if (!is_dir($dynform_template_dir)) {
            $success = mkdir($dynform_template_dir);
            if (!$success) {
                throw new Exception("Unable to create $dynform_template_dir");
            }
        }

        /* make all frontend templates available */
        foreach(glob($this->path.'frontend_templates/*.tpl') as $template) {
            $target = $dynform_template_dir.basename($template);
            if (!file_exists($target)) {
                $success = copy($template, $target);
                if (!$success) throw new Exception("Unable to copy to $target");
            }
        }
    }

    function contentedit_addon($node, $content, $form) {
        if (stristr($form->title, "Dynform_node")) {
            return array('template' => 'dynform_contentedit_addon.tpl', 'data' => array());
        }
        return null;
    }
    
    
    // hooks from aquarius: node delete and node copy
    
    function node_delete($node)
    {
        $dynform = new db_Dynform ; 
        $dynform->node_id = $node->id ; 
        if (!$dynform->find()) return ; 
        $dynform->fetch() ; 
        $block = new db_Dynform_block ; 
        $block->dynform_id = $dynform->id ; 
        $block->find() ; 
        while($block->fetch())
        {
            $field = new db_Dynform_field ; 
            $field->block_id = $block->id ; 
            $field->find() ; 
            while ($field->fetch())
            {
                $field_data = new db_Dynform_field_data ; 
                $field_data->field_id = $field->id ; 
                $field_data->find() ;
                while ($field_data->fetch())
                {
                    $field_data->delete() ; 
                }
                $field->delete() ; 
            }
            $block_data = new db_Dynform_block_data ; 
            $block_data->block_id = $block->id ; 
            $block_data->find() ; 
            while ($block_data->fetch())
            {
                $block_data->delete() ; 
            }
            $block->delete() ; 
        }
        $dynform->delete() ; 
    }
    
    
    function node_copy($source, $destination)
    {
        global $DB;
        $DL = new Dynformlib() ; 
        if (!$DL->is_dynform_node($source)) return ; 

        $source_dynform = new db_Dynform ; 
        $source_dynform->node_id = $source->id ; 
        $res = $source_dynform->find() ; 
        if (!$res) { /* no dynform to copy */ return ; }
        $source_dynform->fetch() ; 
        
        $destination_dynform = new db_Dynform ; 
        $destination_dynform->node_id = $destination->id ; 
        $destination_dynform->insert() ; 
        
        $block = new db_Dynform_block ;
        $block->dynform_id = $source_dynform->id ; 
        $block->find() ; 
        while ($block->fetch())
        {
            $new_block = new db_Dynform_block ; 
            $new_block->dynform_id = $destination_dynform->id ; 
            $new_block->name = $block->name ; 
            $new_block->weight = $block->weight ; 
            $new_block->insert() ; 
            
            $block_data = new db_Dynform_block_data ; 
            $block_data->block_id = $block->id ; 
            $block_data->find() ; 
            while ($block_data->fetch())
            {
                $new_block_data = new db_Dynform_block_data ; 
                $new_block_data->block_id = $new_block->id ; 
                $new_block_data->lg = $block_data->lg ; 
                $new_block_data->name = $block_data->name ; 
                $new_block_data->insert() ; 
            }
            
            $field = new db_Dynform_field ; 
            $field->block_id = $block->id ; 
            $field->find() ; 
            while ($field->fetch())
            {
                $new_field = new db_Dynform_field ; 
                $new_field->block_id = $new_block->id ; 
                $new_field->type = $field->type ; 
                $new_field->name = $field->name ; 
                $new_field->weight = $field->weight ; 
                $new_field->required = $field->required ; 
                $new_field->num_lines = $field->num_lines ; 
                $new_field->width = $field->width ; 
                $new_field->insert() ; 
            
                $field_data = new db_Dynform_field_data ; 
                $field_data->field_id = $field->id ; 
                $field_data->find() ; 
                while ($field_data->fetch())
                {
                    $new_field_data = new db_Dynform_field_data ; 
                    $new_field_data->field_id = $new_field->id ; 
                    $new_field_data->lg = $field_data->lg ; 
                    $new_field_data->name = $field_data->name ; 
                    $new_field_data->options = $field_data->options ; 
                    $new_field_data->insert() ; 
                }
            }
        }
    }



    /** Write a dynform if there is one
      * @param form_node   Node that has the form to be rendered
      * @param content     Content that is used for option fields
      * @param lg          Language to render for
      */
    function render_dynform($params) {
        require_once 'lib/db/Wording.php';

        $DL = new Dynformlib;
        
        $form_node = db_Node::get_node(get($params, 'form_node'));
        if (empty($form_node)) throw new Exception("Unable to load form_node");

        $lg = get($params, 'lg', null); 
        if (empty($lg)) throw new Exception("Missing lg");
    
        $dynform = new db_Dynform ; 
        $dynform->node_id = $form_node->id ;
        $found = $dynform->find() ; 
        
        if ($found) {
            $dynform->fetch() ; 
            $blocks = array() ;
            $dblock = new db_Dynform_block ;
            $dblock->dynform_id = $dynform->id ; 
            $dblock->orderBy('weight ASC') ;
            $dblock->find() ;
            
            // Put dynform data into an array structure suitable for smarty
            $dynform_struct = array();
            $dynform_struct['blocks'] = array();
            
            while ($dblock->fetch()) {
                $block = array();
                $block['title'] = $DL->get_block_name($dblock->id, $lg);
                $block['fields'] = array();
                $dfield = new db_Dynform_field ;
                $dfield->block_id = $dblock->id ;
                $dfield->orderBy('weight ASC') ;
                $dfield->find() ;
                while ($dfield->fetch()) {
                    $field = array();
                    $show = true; // Whether to actually show that field
                    
                    $type = $DL->get_fieldtype_name($dfield->type);
                    $field['type'] = $type;
                    $field['id']   = "field_$dfield->id";
                    $field['labeltofieldid'] = $field['id'];
                    $field['title'] = $DL->get_field_name($dfield->id, $lg);
                    $field['required'] = $dfield->required;
                    $field['width'] = $dfield->width;
                    
                    $options = $DL->get_field_options($dfield->id, $lg);
                    $field['options'] = $options;
                    
                    switch ($type) {
                        case 'Text':
                            $field['text'] = $options;
                            break;

                        case "Checkbox":
                            // There could be multiple checkboxes. To keep their id distinct, a counter is added to their id. Now, which checkbox are we going to refer to from our label? The first of course, because the label would likely be aligned with that one and usually there's only one checkbox anyway.
                            $field['labeltofieldid'] = $field['id']."_0";
                            $field['options'] = array_filter(array_map('trim', explode("\n", $options)), 'strlen');
                            break;

                        case "Radiobutton":
                            $field['options'] = array_filter(array_map('trim', explode("\n", $options)), 'strlen');
                            break;


                        case "Singleline":
                        case "Number":
                        case "Email":
                            $field['classstr'] = "";
                            if ($dfield->required) {
                                switch($type) {
                                    case "Number":     $field['classstr'] = 'require_numeric' ; break;
                                    case "Email":      $field['classstr'] = 'require_email' ;   break;
                                    case "Singleline":
                                    default:
                                                       $field['classstr'] = 'require_text';
                                }
                            }
                            if (!empty($field['options'])) {
                                $field['classstr'] .= ' with_options ' ;
                            }
                            break;


                        case "Multiline":
                            $field['num_lines'] = $field->num_lines;
                            break;


                        case "Pulldown":
                            $field['options'] = array_filter(array_map('trim', explode("\n", $options)), 'strlen');
                            break;


                        case "Option":
                            /* What does this mysterious "Option" field do?
                            * Sometimes the same dynform is used for different nodes.
                            * This happens when you have a lot of pages that need essentially the same dynform, yet visitors must be given a choice specific to that page.
                            * (For example if you have different voyage pages sharing one dynform, but each voyage has its own start dates.)
                            * For such cases a field can be designated as option field and this field will be used by dynform to provide a choice of the different options.
                            * The field may either be a string, or a multi-field. If it's a string, it is split by ';' to get the options. If it's multi, each item is taken as option. */
                            $content = get($params, 'content', null) ;
                            if (!$content) {
                                // Can't read from template vars within insert functions, so we must fail because the content parameter is missing 
                                throw new Exception("render_dynform: please provide content parameter from template (required by option-field)");
                            }
                            $option_field_name = $options;
                            $show = !empty($content->$option_field_name);
                            if ($show) {
                                $field_value = $content->$option_field_name;
                                $options = array();
                                if (is_array($field_value)) {
                                    foreach($field_value as $entry) {
                                        if (is_array($entry)) $options []= first($entry);
                                        else $options []= $entry;
                                    }
                                } else {
                                    $options = array_map('trim', explode(';', $content->$option_field_name));
                                }
                                $show = !empty($options);
                                $field['options'] = $options;
                            }
                            break;

                        case "TargetEmail":
                            $field['options'] = get($this->target_emails($options), 'labels');
                            break;

                        /* Show input field for each node in the tree rooted at
                         * the node given as option. This depends heavily on the
                         * template. */
                        case "Nodelist":
                            $field['nodetree'] = $this->load_nodetree($options, $lg);
                            break;

                        case "Upload":
                            break;

                        default: throw new Exception("dynform field type '$type' not implemented");

                    }

                    if ($show) $block['fields'] []= $field;
                }

                $dynform_struct['blocks'] []= $block;
            }

            // Now, we can't use the usual smarty container because in an {insert}
            // function it is not available. We create a new one for our purposes.
            global $aquarius;
            $smarty = $aquarius->get_smarty_frontend_container($lg, $form_node);

            $smarty->assign('dynform', $dynform_struct);
            $smarty->assign($params);
            
            $smarty->caching = false; // To make sure

            return $smarty->fetch('dynform/form.tpl');
        }


    }
    
    /** Read a submitted dynform and save in DB and send notification mails
      * @param form_node           Node that has te form to be rendered
      * @param lg                  Language to render for
      * @param submit_node_name    Save form submissions under that title
      * @param custom_target_email (Optional) Use this email instead of reading
      *                            it from form_node
      */
    function process_dynform($params) {
        require_once ('lib/db/Wording.php') ;
        require_once ('lib/formatted_mail.lib.php') ;
        
        $DL = new Dynformlib;
        
        $form_node = db_Node::get_node(get($params, 'form_node'));
        if (empty($form_node)) throw new Exception("Unable to load form_node");

        $lg = get($params, 'lg', null); 
        if (empty($lg)) throw new Exception("Missing lg");
        
        $post_vars = clean_magic($_POST);
        $submit_node_name = get($params, 'submit_node_name', null) ;
        $custom_target_email = get($params, 'target_email', null) ; 

        $content = $form_node->get_content() ; 
        $content->load_content() ; 
        
        if ($custom_target_email) {
            $target_email = $custom_target_email ;
        } else {
            $target_email = $content->target_email ; 
        }
        
        // In some places Aquarius erroneously recommends using semicolons to add multiple addresses.
        // We replace those semicolons with commas and tidy up a bit. Yeah yeah, quoted local parts &c not supported.
        $target_email = join(', ', array_map('trim', array_filter(split('[,;]', $target_email))));
        Log::debug("Dynform target_email $target_email");
        
        // Bot blocker: check that the decoy field is empty
        if (!empty($post_vars['email_validate'])) {
            // Act as if everything was alright, but do nothing
            return $content->email_thanx ;
        }

        $client_email = "" ; 
        $mailtxt = array() ; 

        $dynform = new db_Dynform ; 
        $dynform->node_id = $form_node->id ;
        $found = $dynform->find() ;
        if (!$found) throw new Exception("No dynform for ".$form_node->idstr());

        $dynform->fetch() ; 
        $blocks = array() ;
        $block = new db_Dynform_block ;
        $block->dynform_id = $dynform->id ; 
        $block->orderBy('weight ASC') ;
        $block->find();

        $entry_data = array();

        $missing_fields = array();

        while ($block->fetch()) {
            $fields = array() ; 
            $field = new db_Dynform_field ; 
            $field->block_id = $block->id ; 
            $field->orderBy('weight ASC') ;
            $field->find() ;

            $mailtxt[] = $DL->get_block_name($block->id, $lg) ; 
            $mailtxt[] = ' ' ; 

            while ($field->fetch()) {
                $ftype = $DL->get_fieldtype_name($field->type);
                $name = $DL->get_field_name($field->id, $lg);

                $postname = 'field_'.$field->id;
                $value = trim(get($post_vars, $postname, ''));
                if ($field->required) {
                    if (
                        !isset($post_vars[$postname])
                     || strlen($value) == 0
                    ) {
                        $missing_fields []= $name;
                    }
                }

                if ($ftype == "Text") continue ;   // no entries for texts
                elseif ($ftype == "Email") {
                    if (!$client_email) {
                        $client_email = $value;
                        Log::debug("Dynform client_email $client_email");
                        if ($value === $this->conf('test_email')) {
                            $target_email = $this->conf('test_email');
                        }
                    }
                } elseif ($ftype == "TargetEmail") {
                   $target_emails = get($this->target_emails($DL->get_field_options($field->id, $lg)), 'emails');

                    if (isset($target_emails[$value])) {
                        $target_email = $target_emails[$value];
                    } else {
                        // Use first entry when none was chosen
                        $target_email = first($target_emails);
                    }
                    Log::debug("Using target email $target_email");
                } elseif ($ftype == "Checkbox") {
                    if (isset ($post_vars['field_'.$field->id])) {
                        $value = implode ('; ', $post_vars['field_'.$field->id]) ;
                        if (empty($value)) $value = str(new WordingTranslation('yes'));
                    } else {
                        $value = '';
                    }
                } elseif ($ftype == 'Nodelist') {
                    $value = '';
                    $options = $DL->get_field_options($field->id, $lg);
                    $nodeposts = get($post_vars, 'field_'.$field->id, array());
                    $nodetree = $this->load_nodetree($options, $lg);
                    if ($nodetree) {
                        foreach(Nodetree::flatten($nodetree) as $node_entry) {
                            $order_node = $node_entry['node'];
                            $posted = get($nodeposts, $order_node->id);
                            if (!empty($posted)) {
                                $order_content = $order_node->get_content($lg);
                                if (!$order_content) continue; // ignorant error handling

                                $title = trim($order_content->form_code);
                                if ($title == '') $title = $order_content->title();
                                $value .= sprintf("\r\n% 4s: %s", $posted, $title);
                            }
                        }
                    }
                } elseif ($ftype == 'Upload') {
                    $field_name = 'field_'.$field->id;
                    if ($_FILES[$field_name]) {
                        // The dir where the files are stored must be
                        // random so that it can't be guessed.
                        $chars = 8;

                        $rbytes = '';
                        if (function_exists('openssl_random_pseudo_bytes')) {
                            $rbytes = openssl_random_pseudo_bytes($chars);
                        } else {
                            // Because we don't have openssl_random_pseudo_bytes we'll
                            // just use uniqid() and bray.
                            $rbytes = uniqid('', true);
                        }
                        
                        $rstr = substr(md5($rbytes), $chars * -1); // Depending on the function used above we get either binary or hex output, trash it through md5 to get a uniform hex string

                        $month_path = $this->conf('upload_dir').strftime('%Y/%m/'); // Grouping dirs by month makes it easier to delete old dirs
                        $path = $month_path.$rstr.'/';
                        $full_path = FILEBASEDIR.$path;
                        if (is_dir($full_path)) {
                            // This does not happen for big $chars
                            throw new Exception("Errord $path exists. This happens ".count(scandir($full_path))." out of ".(1<<(4*$chars))." times. It's like winning the lottery, and you too get a price. Your price is this error message. Enjoy. And do try again."); // Hex carries four bits of information per character
                        }
                        $created = mkdir($full_path, 0755, true);

                        if (!$created) throw new Exception("Unable to create $path");
                        
                        require_once "lib/file_mgmt.lib.php";
                        $mresult = process_upload($_FILES[$field_name], $path);
                        
                        // Now dynform has no way of gracefully handling errors, it always works unless it doesn't
                        // I go with the flow
                        if ($mresult['error'] && $mresult['message']) throw new Exception('Upload error '.first($mresult['message']));
                        
                        $filename = get($mresult, 'new_name');
                        if ($filename) {
                            // Takin a wild guess
                            $value = 'http://'.$_SERVER['SERVER_NAME'].'/'.$path.$filename;
                        } else {
                            $value = '';
                        }
                    }
                }

                $entry_datum = new db_Dynform_entry_data;
                $entry_datum->field_id = $field->id;
                $entry_datum->name = $name;
                $entry_datum->value = $value;
                $entry_data []= $entry_datum;

                $mailtxt []= array($name, $value);
            }
            $mailtxt []= ' ';
        }

        // We still don't have proper error handling when client validation fails :-(
        // Spambots fail to do client validation (surprise?) so we throw an error
        if ($missing_fields) throw new Exception("Required field missing: ".join(', ', $missing_fields));

        $df_entry = new db_Dynform_entry ; 
        $df_entry->dynform_id = $dynform->id ; 
        $df_entry->time = self::toMysqlDatetime($_SERVER['REQUEST_TIME']) ;
        $df_entry->lg = $lg ; 
        $df_entry->submitnodetitle = empty($submit_node_name) ? $content->title : $submit_node_name;
        $df_entry->insert() ;

        foreach ($entry_data as $datum) {
            $datum->entry_id = $df_entry->id;
            $datum->insert();
        }

        if ($target_email == "") {
            throw new Exception("Target email for form is missing!") ;
        }
        
        // Take the first address as sender address if there are multiple
        $sender = trim(first(explode(',', $target_email)));
        
        $this->send_mail($target_email, $client_email, $sender, $content, $submit_node_name, $mailtxt, $confirmation=false);

        
        if ($content->send_confirmation_mail && $client_email) {
            if ($content->email_confirmation_sender) $conf_sender = $content->email_confirmation_sender ; 
            else $conf_sender = "info@".$_SERVER['SERVER_NAME'];
            $this->send_mail($client_email, $conf_sender, $conf_sender, $content, $submit_node_name, $mailtxt, $confirmation=true);
        }

        return nl2br($content->email_thanx); 
    }


    private function send_mail($recipient, $replyto, $sender, $content, $submit_node_name, $rows, $confirmation=false) {
        $subject = $content->email_subject;
        if (!empty($submit_node_name)) $subject .= ' | '.$submit_node_name;

        $newMail = new FormattedHTMLMail($recipient, $subject, $sender);
        if ($replyto) {
            $newMail->setReplyAddress($replyto);
            $newMail->setSenderAddress($replyto);
        }

        if ($confirmation) {
            $newMail->addText($content->email_confirmation_text);
            $newMail->addText("");
            if ($content->email_confirmation_subject) {
                $newMail->setSubject($content->email_confirmation_subject); 
            }
        } else {
            $newMail->addText($subject);
        }

        $newMail->nextBlock(100, array(40, false));
        $newMail->addDelimiter();

        foreach($rows as $key => $txt) {
            if (is_array($txt)) {
                $newMail->addTextRow($txt[0], $txt[1]);
            } else {
                $newMail->addText($txt);
            }
        }

        $newMail->addDelimiter();
        return $newMail->sendMail(); 
    }


    static function toMysqlDatetime($timestamp=''){
        // Array passed? convert server time and gmt_time in array
        if (is_array($timestamp) AND isset($timestamp['server_time']) AND isset($timestamp['gmt_time'])) {
            $timestamp['server_time'] = $this->toMysqlDatetime($timestamp['server_time']);
            $timestamp['gmt_time'] = $this->toMysqlDatetime($timestamp['gmt_time']);
            return $timestamp;
        } else {
            return date("Y-m-d H:i:s", $timestamp);
        }
    }
    
    function load_nodetree($root_id, $lg) {
        $node = db_Node::get_node($root_id);
        if ($node) {
            $filters = array(
                NodeFilter::create('has_content', $lg), 
                NodeFilter::create('active_self', true)
            );
            return NodeTree::build($node, array(), Nodefilter::create('and', $filters));
        }
    }
    
    /** Parse the option string of the target email field */
    function target_emails($optionstr) {
        $options = array_filter(array_map('trim', explode("\n", $optionstr)));
        $emails = array();
        $labels = array();
        foreach ($options as $email_label) {
            $parts = array_filter(array_map('trim', explode(',', $email_label)));
            if (count($parts) == 2) {
                list($label, $email) = $parts;
                $labels []= $parts[0];
                $emails []= $parts[1];
            } else {
                // Ignore whatever invalid data is on this line
            }
        }

        return compact('emails', 'labels');
    }
}


/** Generate HTML code for a dynform and process form submission.
  *
  * @param form_node   Node that has the form to be rendered
  * @param lg          Language to render for
  * @param submit_name Save form submissions under that title
  *
  * POST parameters are checked for the key 'dynform_submit', and the form is
  * processed when this string shows up.
  * 
  * This is a smarty {insert} function so that it works well on otherwise cached
  * pages. Usage:
  *  
  *     {insert name="dynform_here" form_node=$node->id lg=$lg submit_name=$title}
  */
function insert_dynform_here($params) {
    $params['submit_node_name'] = $params['submit_name'];

    global $aquarius;
    $dynform_mod = $aquarius->modules['dynform'];
    if (isset($_POST['dynform_submit'])) {
        // This shouldn't actually be started from a template, it's hard to
        // correct now
        return $dynform_mod->process_dynform($params);
    } else {
        return $dynform_mod->render_dynform($params);
    }
}
