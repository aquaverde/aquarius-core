<?php
/*	Formated Text Mail

    Class designed to easely create formated text mail.

    A mail contains one or more blocks.
    A block can be a simple text or a space separated table.

    Example:
    --------------------------------------------------------

    'Block1'
    test asfasdf lkajdsf lkadjsfl aksjdfl kasdjf
    as flkajsdflk ajsdflaks jdflkasjdf lkasdjf
    as lfkjasdlf kjasdlfj aldskfjldfaks j
    'Block1 end'
    'Block2'
    KeyText:             Value1
    KeyTextASDF:         Value2
    ASflakjsdf:          Value3
    'Block2 end'
    --------------------------------------------------------
*/

class FormattedTextMail {
    protected $mailText		= '';
    protected $blockColumns	= array();
    protected $currentColumn	= 0;
    
    protected	$targetAddress;
    protected $senderAddress;
    protected $subject;
    
    public function __construct($targetAddress, $subject, $senderAddress)
    {
        $this->targetAddress	= $targetAddress;
        $this->senderAddress	= $senderAddress;
        $this->subject			= $subject;
    }
    
    public function setTargetAddress($targetAddress)
    {
   		$this->targetAddress = $targetAddress;
    }
    
    public function setSenderAddress($senderAddress)
    {
    	$this->senderAddress = $senderAddress;
    }
    
    public function setSubject($subject)
    {
    	$this->subject = $subject;
    }
    
    public function sendMail() {
        $to = $this->targetAddress;
        if (empty($to)) throw new Exception("Error sending Mail: targetEmail empty!");
        
        require_once 'lib/swift/swift_required.php';
        
        $message = Swift_Message::newInstance();

        $text = $this->mailText;
        $message->setBody($text);
        
        $message->setFrom($this->senderAddress);
        
        $message->setSubject($this->subject);

        $tos = array_map('trim', explode(',', $to));
        $message->setTo($tos);

        $logstr = "mail to $to \n".$message->getHeaders()->toString()."\n\n".$text;

        $mailer = Swift_Mailer::newInstance(Swift_MailTransport::newInstance());
        $success = $mailer->send($message);

        if ($success) {
            Log::info("Sent $logstr");
        } else {
            Log::warn("Failed sending $logstr");
        }
        return $success;
    }
    
    // clear the last blocks settings
    public function nextBlock($maxWidth, $column_sizes)
    {
        $this->blockColumns		= $column_sizes;
        $this->maxBlockWidth	= $maxWidth;
    }

    public function addText($text) {
        $this->mailText .= $text."\n";
    }

    /** Add a row of text.
      * String Params are put into columns in the same order they were received.
      */
    public function addTextRow() {
        // Build a format string that pads to the right
        $format = "";
        foreach($this->blockColumns as $column_width) {
            if (is_numeric($column_width)) {
                $format .= "% -{$column_width}s";
            } else {
                $format .= "%s";
            }
        }

        // Get the text fields for this row and replace empty fields with a dash
        $params = func_get_args();
        $params = array_map(create_function('$field', 'return empty($field)?"-":$field;'), $params);

        // Call sprintf to format the fields
        array_unshift($params, $format);
        $this->mailText .= call_user_func_array('sprintf', $params)."\n";
    }
    
    public function addDelimiter($delimiter = '-')
    {
        $this->mailText .= str_repeat($delimiter, $this->maxBlockWidth)."\n";
    }
    
    public function addTextBlock($text)
    {
        $this->mailText.= $text."\n";
    }
    
    public function getMailText()
    {
        return $this->mailText;
    }
    
}

/** Same interface as FormattedTextMail, but send HTML as well as Text part.
  * It also uses the Swift Mailer to send the mails, so hopefully they are not
  * sent out broken to begin with.
  */
class FormattedHTMLMail extends FormattedTextMail {
    var $htmltext = '';
    var $table_open = false;
    
    public function sendMail() {
        $to = $this->targetAddress;
        if (empty($to)) throw new Exception("Error sending Mail: targetEmail empty!");
        
        require_once 'lib/swift/swift_required.php';
        
        $message = Swift_Message::newInstance();

        $text = $this->getMailText();
        $message->setBody($text);
        $message->addPart($this->getMailHTML(), 'text/html');
        
        $message->setFrom($this->senderAddress);
        
        $message->setSubject($this->subject);

        $tos = array_map('trim', explode(',', $to));
        $message->setTo($tos);

        $logstr = "mail to $to \n".$message->getHeaders()->toString()."\n\n".$text;

        $mailer = Swift_Mailer::newInstance(Swift_MailTransport::newInstance());
        $success = $mailer->send($message);

        if ($success) {
            Log::info("Sent $logstr");
        } else {
            Log::warn("Failed sending $logstr");
        }
        return $success;
    }
        
    protected function ensure_closed_table() {
        if ($this->table_open) {
            $this->htmltext .= "</table>\n";
            $this->table_open = false;
        }
    }
    
    protected function ensure_open_table() {
        if (!$this->table_open) {
            $this->htmltext .= "<table>\n";
            $this->table_open = true;
        }
    }
    
    public function nextBlock($maxWidth, $column_sizes) {
        $this->ensure_closed_table();
        parent::nextBlock($maxWidth, $column_sizes);
    }

    public function addText($text) {
        $this->ensure_closed_table();
        $this->htmltext .= htmlentities($text, ENT_QUOTES, 'UTF-8')."\n";
        parent::addText($text);
    }

    /** Add a row of text.
      * String Params are put into columns in the same order they were received.
      */
    public function addTextRow() {
        $this->ensure_open_table();
    
        $params = func_get_args();
    
        $this->htmltext .= "  <tr>\n";
        $sizes = $this->blockColumns;
        foreach($params as $entry) {
            $size = array_shift($sizes) * 5; // Just assume 5px per char
            $this->htmltext .= "    <td ".($size?"width='{$size}'":'').">".nl2br(htmlentities($entry, ENT_QUOTES, 'UTF-8'))."</td>\n";
        }
        $this->htmltext .= "  </tr>\n";
        
        return call_user_func_array(array(parent, addTextRow), $params);
        
    }
    
    public function addDelimiter($delimiter = '-') {
        $this->ensure_closed_table();
        if ($delimiter == '-') {
            $this->htmltext .= "<hr/>\n";
        } else {
            $this->htmltext .= str_repeat(htmlentities($delimiter, ENT_QUOTES, 'UTF-8'), $this->maxBlockWidth)."\n";
        }
        return parent::addDelimiter($delimiter);
    }
    
    public function addTextBlock($text) {
        $this->ensure_closed_table();
        $this->htmltext .= '<p>'.htmlentities($text, ENT_QUOTES, 'UTF-8')."</p>\n";
        return parent::addTextBlock($text);
    }
    
    public function getMailHTML() {
        return '<html><body>'.$this->htmltext.'</body></html>';
    }
}

	
?>