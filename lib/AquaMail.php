<?php 
/** @package Aquarius */

/** A mail built from smarty templates
  * There are multiple values used that may be assigned in the smarty template or using set():
  *   from, to:         Email addresses
  *   replyto, cc, bcc: Optional addresses
  *   subject:          String
  *   html_image*:      paths to image files to be included as HTML image attachements
  *
  * Charset for everything is UTF 8, you're not using anything else, are you?
  *
  * Example, demonstrating that subject, from, to, etc. can be set anywhere:
  * <code>
  * File: html.tpl
  *   {assign name="subject" var="Cool Subject!" scope=root}
  *   {assign name="html_image1" var="bildli.jpg"}
  *   <div>{$text} <img src="{$html_image1}"/></div>
  *   <div>{$moretext}</div>
  *   Cheers {$from}
  *
  * File: text.tpl
  *   {$text}
  *   {$moretext}
  *   Cheers {$from}
  *
  * File: main.php
  *   $smarty = new Smarty();
  *   $smarty->assign('from', '"Egg Ham" <egg@example>');
  *   $smarty->assign('text', 'Caution: breathing may be hazardous to your health.');
  *   $smarty->assign('moretext', 'You too can wear a nose mitten.');
  *   $mail = new AquaMail($smarty, 'text.tpl', 'html.tpl');
  *   $mail->set('to', 'friend@example');
  *   $mail->set('cc', 'otherfriend@example, yetanotherfriend@example');
  *   $mail->send();
  * </code>
  */
class AquaMail {
    private $values;

    private $text_body;
    private $html_body;

    /** Create a mail */
    function __construct($smarty, $text_template, $html_template=false) {
        if ($text_template) {
            if (!$smarty->template_exists($text_template)) throw new Exception("Missing template $text_template");
            $this->text_body = $smarty->fetch($text_template);
        }
        if ($html_template) {
            if (!$smarty->template_exists($html_template)) throw new Exception("Missing template $html_template");
            $this->html_body = $smarty->fetch($html_template);
        }
        $this->values = $smarty->get_template_vars();
    }

    /** Set value(s) */
    function set($key, $value=null) {
        if (func_num_args() == 2) $this->values[$key] = $value;
        else $this->values = array_merge($this->values, $key);
    }

    function send() {
        $message = Swift_Message::newInstance();

        // Attach pics and other files as requested
        foreach($this->values as $key => $value) {
            if ($this->html_body && preg_match('/^html_image/i', $key)) {
                $image_cid = $message->embed(Swift_Image::fromPath($value));
                
                // replace filename in HTML body with ID of generated attachement
                $this->html_body = str_replace($value, $image_cid);
            }

            if (preg_match('/^file_attachment/i', $key)) {
                switch(pathinfo($value, PATHINFO_EXTENSION)) {
                case 'jpg':
                case 'png':
                    $result = $message->embed(Swift_Image::fromPath(FILEBASEDIR.$value));
                    break;
                default:
                    $result = $message->attach(Swift_Attachment::fromPath(FILEBASEDIR.$value));
                }
            }
        }

        if ($this->text_body) $message->setBody($this->text_body);
        if ($this->html_body) $message->addPart($this->html_body, 'text/html');
        
        
        $cc = get($this->values, 'cc');
        if ($cc)  $message->setCc($cc);
        
        $bcc = get($this->values, 'bcc');
        if ($bcc) $message->setBcc($bcc);
        
        // Determine the sender address used for the return-path on the envelope
        // and Sender: header.
        $sender = get($this->values, 'sender');

        global $aquarius;

        // DEPRECATED The sender address may be set in email/smtp/sender
        $smtp_sender = $aquarius->conf('email/smtp/sender');

        // When the sender is set in the SMTP config, it is always forced DEPRECATED
        $force_sender = $aquarius->conf('email/force_sender') || $smtp_sender;
        if (!$sender || $force_Sender) {
            // If deprecated smtp/sender is set, use that
            $sender = $smtp_sender ? $smtp_sender : $aquarius->conf('email/sender');

            // Add domain if only local part was supplied
            if (strpos($sender, '@') === false) {
                $sender .= '@'.preg_replace('/^www./', '', $_SERVER['SERVER_NAME']);
            }
        }

        $message->setReturnPath($sender);
        $message->setSender($sender);
        
        $from = get($this->values, 'from');
        if ($from) $message->setFrom($from);
        else $message->setFrom($sender);
        
        $message->setSubject(get($this->values, 'subject', ''));

        $to = get($this->values, 'to');
        if (strlen($to)) $message->setTo($to);

        $replyto = get($this->values, 'replyto');
        if ($replyto) $message->setReplyto($replyto);

        $logstr = "mail to $to on behalf of ".$_SERVER['REMOTE_ADDR']."\n".$message->getHeaders()->toString()."\n\n".$this->text_body;
        $mailer = $aquarius->mailer();

        $success = false;
        try {
            $success = $mailer->send($message);
        } catch(Swift_TransportException $e) {
            Log::warn($e);
        }
        if ($success) {
            Log::info("Sent $logstr");
        } else {
            Log::warn("Failed sending $logstr");
        }
        return $success;
    }
}
