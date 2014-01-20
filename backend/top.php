<?php
/** Backend top frame
  * @package Aquarius.backend
  */

require '../lib/init.php';
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

$smarty->assign("clean_cache", Action::make('cache_cleaner', 'all'));

$debug_mode = Log::DEBUG;
$debug_text = "SET DEBUG";
if ($aquarius->debug()) {
    $debug_mode = Log::NEVER;
    $debug_text = "DEBUG ACTIVE";
}
require_once "action_decorators.php";
$debug_action = Action::build(array('echo_cookie', 'set'), array('loglevel' => $debug_mode, 'firelevel' => Log::NEVER));
if ($debug_action) $debug_action = new ActionTitleChange($debug_action, $debug_text);

$smarty->assign("set_debug", $debug_action);
$smarty->assign("userId", $user->id);
$smarty->assign("user", $user);
$smarty->assign("url", $url);
$smarty->assign("lg", $lg);
$smarty->assign("frameset_url", $frameset_url);
$smarty->assign("languages", db_Users2languages::getLanguagesForUser($user));
$smarty->display('top.tpl');
