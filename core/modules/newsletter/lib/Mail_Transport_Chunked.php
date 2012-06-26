<?
/** Send mail to many recipients by splitting recipients list into chunks and sending the mail separately to each. */
class Mail_Transport_Chunked {
    /** @param $transport a PEAR Mail instance that will be used to send the mails
      * @param $max_rcpt Maximum of allowed recipients per mail. Default is 100
                         because that is the minimum a RFC2821-compliant server
                         must support. In some cases this number must be lowered.
      * @param $from If set: overrides From: address for all mails
      * @param $delay_per_rcpt Sleep this many seconds per recipient between
      *                        sending chunks. This annoyance is neccessary for
      *                        some rate-limited mailservers.
      */
    function __construct($transport, $max_rcpt = 100, $from = false, $delay_per_rcpt=0) {
        $this->transport = $transport;
        $this->max_rcpt = $max_rcpt;
        $this->from = $from;
        $this->delay_per_rcpt = $delay_per_rcpt;
    }

    /** Send a message to a bunch of addresses.
      * The recipient list is split into chunks and the message is sent to recipients of each chunk separately. This is neccessary because many mail relays limit the amount of recipients permitted in a message.
      *
      * @param $recipients a list of email addresses to send to. Plain mail addresses only, RFC822 quoted stuff currently not supported.
      * @param $message something that responds to header() and body() calls.
      * @param $notify_on_send When present, this function is called with each chunk of addresses when a mail was sent to them.
      * @return the recipients count
      *
      * Mail headers are converted to the ISO-8859-1 encoding. In 2010 there is still no reliable way (with the tools we use) to prepare mails in Unicode, especially the headers.
      *
      */
    function send($recipients, $message, $notify_on_send = false) {
        $mime = new Mail_mime("\n"); // For some reason the Pear Mail class expects newline-only mails
        $mime->setHTMLBody($message->body());
        $build_params = array(
            'html_charset' => 'utf-8',
            'text_charset' => 'utf-8',
            //    'text_encoding' => 'base64',
            'head_charset' => 'ISO-8859-1'
        );

        $mail_body   = $mime->get($build_params);

        // Force header to ISO-8859-1
        $header = $message->header();
        if (!empty($this->from)) $header['From'] = $this->from;
        foreach($header as &$value) { // Using dodgy alias syntax for fun and profit
            $value = utf8_decode($value);
        }
        $mail_header = $mime->headers($header);

        $grouped_by_domain = array(); // The list of recipients is grouped by domain so that mails to the same domain are sent together. This makes delivery a lot more efficient for mail relays.
        foreach($recipients as $recipient) {
            $address_parts = Mail_RFC822::isValidInetAddress($recipient);
            if ($address_parts == false) {
                throw new Exception("Invalid mail address in list of recipients: '$recipient'");
            } else {
                $grouped_by_domain[strtolower($address_parts[1])] []= $address_parts[0].'@'.$address_parts[1];
            }
        }
        $ordered = array_flatten($grouped_by_domain);
        foreach(array_chunk($ordered, $this->max_rcpt) as $index => $recipients_chunk) {
            if ($this->delay_per_rcpt > 0 && $index !== 0) {
                $delay = $this->max_rcpt * $this->delay_per_rcpt;
                Log::debug("Delaying sending the next batch by {$delay}s, please be patient.");
                sleep($delay);
            }
            Log::debug("Sending mail to ".print_r($recipients_chunk, true));
            $send_report = $this->transport->send($recipients_chunk, $mail_header, $mail_body);

            if (PEAR::isError($send_report)) { 
                throw new Exception($send->getMessage());
            }

            if ($notify_on_send) call_user_func($notify_on_send, $recipients_chunk);
        }

        return count($recipients);
    }
}
?>