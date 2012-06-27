<?php
/** CMS initialization used both in frontend and backend.
  * Initialization prepares the following things:
  * - Load config files
  * - logging
  * - Add the directory 'aquarius/' as PHP include path
  * - PEAR DataObject database connection
  * @package Aquarius
  */


require_once 'Aquarius_Loader.php';
$loader = new Aquarius_loader();
$aquarius = $loader->init('full');
$aquarius->load();
