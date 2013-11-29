<?php 

function errorHandler($error)
{
    $message = $error->getMessage();
    if (!empty($error->backtrace[1]['file'])) {
        $message .= ' (' . $error->backtrace[1]['file'];
        if (!empty($error->backtrace[1]['line'])) {
            $message .= ' at line ' . $error->backtrace[1]['line'];
        }
        $message .= ')';
    }
    Log::warn($message);
}

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, 'errorHandler');


function newslettermail($from, $to, $bcc, $subject, $content) 
{
	require_once('Mail.php');
	require_once('Mail/mime.php');
    
    $crlf = "\n";
    Log::debug("From: ".$from);
    Log::debug("To: ".$to);
    Log::debug("Subject: ".$subject);
    Log::debug("Body: ".$content);
    $hdrs = array(
                    'From' => $from,
                    'Subject' => utf8_decode($subject)
                    );
    
    if ($bcc) $hdrs['Bcc'] = $bcc;
    if ($to) $hdrs['To'] = $to; 
    
    $mime = new Mail_mime($crlf);
    $mime->setHTMLBody(utf8_decode($content));
    $body = $mime->get();
    $hdrs = $mime->headers($hdrs);
    $smtpParams = array(
                        'host' => SMTP_HOST,
                        'auth' => SMTP_AUTH,
                        'password' => SMTP_PASSWORD,
                        'username' => SMTP_USER,
                        'debug' => SMTP_DEBUG
                        );
                
//  $mail =& Mail::factory('smtp', $smtpParams);
    $mail = Mail::factory('mail');
    $result = $mail->send($to, $hdrs, $body);
    return $result;

}

function newslettermassmail($from, $to, $bcc, $subject, $content) {
    return newslettermail($from, $to, $bcc, $subject, $content);
}
?>