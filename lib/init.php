<?php
/** CMS initialization used both in frontend and backend.
  * Initialization prepares the following things:
  * - Load config files
  * - logging
  * - Add the directory 'aquarius/' as PHP include path
  * - PEAR DataObject database connection
  * @package Aquarius
  */

#ini_set('display_errors','1');
#error_reporting(E_ALL);

require dirname(__FILE__).DIRECTORY_SEPARATOR.'Aquarius_Frontloader.php';

$frontloader = new Aquarius_Frontloader();
$loader = $frontloader->load('full');

$aquarius->load();
