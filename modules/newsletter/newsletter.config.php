<?php

// NEWSLETTER
if (!defined('NEWSLETTER_ROOT_NODE')) define('NEWSLETTER_ROOT_NODE', '');
define('ADMIN_NEWSLETTER_ADDRESSES_PER_PAGE', 100);
define('NEWSLETTER_CLEAN_DELTA', 7*24*60*60); // when is an inactive address to be deleted

define('NEWSLETTER_CSS', FILEBASEDIR.'/css/newsletter.css') ; 

/*
$config['newsletter']['transport'] = array(
    'type'     => 'smtp',
    'host'     => 'smtp.bulkspam.example',
    'username' => 'egg@bulkspam.example',
    'password' => 'ham',
    'from'     => 'egg@bulkspam.example',
    'auth'     => true,
    'debug'    => false,
    'max_rcpt' => 1,
    'delay_per_rcpt' => 1
);

// perl fakesmtpd.pl
$config['newsletter']['transport'] = array(
    'type'     => 'smtp',
    'host'     => 'localhost',
    'port'     => '2525',
    'from'     => 'test@bulkspam.example',
    'max_rcpt' => 1,
    'delay_per_rcpt' => 0
);
*/