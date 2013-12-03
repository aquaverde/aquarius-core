<?php 

/** Send mail when a form is POSTed.
  * This is a typical config:
  * $config['mailform']['forms'][] = array(
  *    'form_name'            => 'contactform', // Name of the form, if a POST variable with this name shows up, mailform expects this form to be posted
  *    'node'                 => 'contact'      // id or name of node where fields related to the form are searched. This node and its content fields will be available in the mail templates.
  *    'target_address_field' => 'form_email'   // Name of the field containing the email address the form data will be sent to.
  *    'replyto_formfield'    => 'email'        // Name of the form field whose value will be added as 'Reply-To: ' header
  *    'test_email'           => 'test@aquaverde.ch'           // If this address shows up in the replyto_formfield, the form is sent to this address, not to the one in target_address_field.
  *    'sender_address'       => '"Kontaktformular" <>',
  *    'text_template'        => 'contactform.mail.txt.tpl' , // Name of the mail template, text version
  *    'html_template'        => 'contactform.mail.html.tpl', // Name of the mail template, html version
  *    'formfields'           => array(         // What fields to expect in the form, in the format of the validate() function.
                            'name' => 'string',
                            'email' => 'string',
                            'message' => 'string empty'
        )
  * );
  *
  * Multiple forms are supported.
  *
  * Other options:
  *
  * decoy_field: Name of a field where if it is missing or non-empty mailform
  *              acts as if it did send the mail but doesn't.
  *  
  * The configured $node, $content and its fields will be available in the mail
  * template. Also the fields from 'formfields' will be available in the 
  * $formfields dict.
  */
class MailForm extends Module {
    var $register_hooks = array('frontend_page', 'smarty_config_frontend') ;
    var $short = "mailform" ;
    var $name  = "Form mailer" ;

    var $sent_status  = array();

    function frontend_interface() {
        return $this;
    }

    /** Checks for POSTed forms and sends mail for those */
    function frontend_page($smarty) {
        global $aquarius;
        foreach($aquarius->conf('mailform/forms', array()) as $index => $form_config) {
            $form_name = $form_config['form_name'];
            if (strlen($form_name) < 1) {
                Log::warn("Mailform config forms $index missing form_name");
            } else {
                if (isset($_POST[$form_name])) {
                    $sent = $this->send($form_config);
                    $this->sent_status[$form_name] = $sent ? 'success' : 'error';
                }
            }
        }
    }

    /** Send a mail for the given form config*/
    function send($formspec) {
        $form_name = $formspec['form_name'];
    
        // Load the node with info on where to send the form
        // This node must be specified explicitly in the config. It is not read
        // from the request by default because of security concerns.
        $node_name = $formspec['node'];
        $node = db_Node::get_node($node_name);
        if (!$node) throw new Exception("Unable to load node '$node_name' for $form_name");

        $content = $node->get_content();
        $content->load_fields();

        $replyto_address = requestvar($formspec['replyto_formfield']);
        
        // Determine target email address
        $field_name = $formspec['target_address_field'];
        $target_address = $content->$field_name;
        if (strlen($target_address) < 1) throw new Exception("No target mail address in node '$node_name' field '$field_name' for $form_name");
        
        $test_email = get($formspec, 'test_email');
        if ($test_email == $replyto_address) {
            $target_address = $test_email;
        }
        
        $formfield_errors = array();
        $post = clean_magic($_POST);
        $formfields = validate($post, $formspec['formfields'], $formfield_errors);
        $this->field_errors[$form_name] = $formfield_errors;
        if (count($formfield_errors) > 0) {
            Log::debug("Invalid fields:");
            Log::debug($formfield_errors);
            return false;
        }
        
        global $aquarius;
        $smarty = $aquarius->get_smarty_frontend_container(false, $node);
        $smarty->assign('formfields', $formfields);

        require_once "lib/aquamail.php";
        $mail = new AquaMail($smarty, get($formspec, 'text_template'), get($formspec, 'html_template'));
        $mail->set('to', $target_address);
        $mail->set('from', $formspec['sender_address']);
        if ($replyto_address) $mail->set('replyto', $replyto_address);

        $decoy_field = get($formspec, 'decoy_field', false);
        if ($decoy_field) {
            if (!isset($post[$decoy_field])) {
                Log::debug("Mailform decoy field '$decoy_field' missing from POST. Not sending mail.");
                return true;
            }
            if (!empty($post[$decoy_field])) {
                Log::debug("Mailform decoy field '$decoy_field' has content '".$post[$decoy_field]."' in POST. Not sending mail.");
                return true;
            }
        }

        return $mail->send();

    }

    /** Query status from smarty templates.
      * Params:
      *   form_name: name of the form to check
      *
      * Returns false if the form was not sent, 'success' if the form was sent
      * successfully and 'error' when sending was attempted but an error
      * occurred. */
    function sent_status($params) {
        $form_name = $params['form_name'];
        if (!isset($this->sent_status[$form_name])) return false;
        return $this->sent_status[$form_name];
    }
}
?>