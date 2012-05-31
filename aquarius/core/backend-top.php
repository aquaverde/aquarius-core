<?php
/** Backend top frame
  * @package Aquarius.backend
  */

require 'backend.php';

// We do not need sessions around here, unblock it.
session_write_close();

$smarty = $aquarius->get_smarty_backend_container();

$frameset_url = new Url("index.php");
$frameset_url->add_param('lg', $lg);
$url = new Url("admin.php");
$url->add_param('lg', $lg);

require_once 'lib/menu.php';
$smarty->assign('menu', Menu::make($lg));

$smarty->assign("userId", $user->id);
$smarty->assign("user", $user);
$smarty->assign("url", $url);
$smarty->assign("lg", $lg);
$smarty->assign("frameset_url", $frameset_url);
$smarty->assign("languages", db_Users2languages::getLanguagesForUser($user));
$smarty->display('top.tpl');
?>