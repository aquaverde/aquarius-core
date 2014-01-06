<?php
$config['mailform']['forms'] = array();

/* Example:
$config['mailform']['forms'][] = array(
    'form_name'            => 'contactform', // Name of the form, if a POST variable with this name shows up, mailform expects this form to be posted
    'node'                 => 'contact'      // id or name of node where fields related to the form are searched. This node and its content fields will be available in the mail templates.
    'target_address_field' => 'form_email'   // Name of the field containing the email address the form data will be sent to.
    'replyto_formfield'    => 'email'        // Name of the form field whose value will be added as 'Reply-To: ' header
    'sender_address'       => '"Kontaktformular" <>',
    'text_template'        => 'contactform.mail.txt.tpl' , // Name of the mail template, text version
    'html_template'        => 'contactform.mail.html.tpl', // Name of the mail template, html version
    'formfields'           => array(         // What fields to expect in the form, in the format of the validate() function.
        'name' => 'string',
        'email' => 'string',
        'message' => 'string empty'
    )
);
*/