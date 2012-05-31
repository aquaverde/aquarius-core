<?php

// NEWSLETTER
if (!defined('NEWSLETTER_ROOT_NODE')) define('NEWSLETTER_ROOT_NODE', '');
define('ADMIN_NEWSLETTER_ADDRESSES_PER_PAGE', 100);
define('NEWSLETTER_ADDRESS_EXPORT', FILEBASEDIR.'/download/newsletter_addresses.xls');
define('NEWSLETTER_ADDRESS_EXPORT_URL', PROJECT_URL.'/download/newsletter_addresses.xls');
define('NEWSLETTER_CLEAN_DELTA', 7*24*60*60); // when is an inactive address to be deleted

define('NEWSLETTER_CSS', FILEBASEDIR.'/css/newsletter.css') ; 

?>