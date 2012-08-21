<?php
/** CMS initialization used both in frontend and backend.
  * Initialization prepares the following things:
  * - Load config files
  * - logging
  * - Add the directory 'aquarius/' as PHP include path
  * - PEAR DataObject database connection
  * @package Aquarius
  */

require_once 'Aquarius_Frontloader.php';
$loader = new Aquarius_Frontloader();

$loader->load('full');
$aquarius->load();
