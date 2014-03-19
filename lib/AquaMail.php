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
  *   {assign name="subject" var="Cool Subject!"}
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
            if ($this->html_body && eregi('^html_image', $key)) {
                $image_cid = $message->embed(Swift_Image::fromPath($value));
                
                // replace filename in HTML body with ID of generated attachement
                $this->html_body = str_replace($value, $image_cid);
            }
            if (eregi('^file_attachment', $key)) {
                $message->embed(Swift_Image::fromPath($value));
            }
        }

        if ($this->text_body) $message->setBody($this->text_body);
        if ($this->html_body) $message->addPart($this->html_body, 'text/html');
        
        
        $cc = get($this->values, 'cc');
        if ($cc)  $message->setCc($cc);
        
        $bcc = get($this->values, 'bcc');
        if ($bcc) $message->setBcc($bcc);
        
        $sender = get($this->values, 'sender');
        if (!$sender) {
            global $aquarius;
            $sender = $aquarius->conf('email/sender');
            if (strpos('@', $sender) === -1) {
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

        if ($sender = $aquarius->conf('email/smtp/sender')) $message->setFrom($sender);
        $mailer = $aquarius->mailer();

        $success = $mailer->send($message);

        if ($success) {
            Log::info("Sent $logstr");
        } else {
            Log::warn("Failed sending $logstr");
        }
        return $success;
    }
}
