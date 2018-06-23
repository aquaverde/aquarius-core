<?php
/** Backend frameset
  * @package Aquarius.backend
  */

require '../lib/init.php';
require 'backend.php';

// We do not need sessions around here, unblock it.
session_write_close();

$smarty = $aquarius->get_smarty_backend_container();

require_once 'lib/menu.php';
$menu_root = Menu::make($lg);

$display = requestvar('display');
$menu_entry = false;
if ($display) {
    $menu_entry = $menu_root->get_entry($display);
}

// Use the first menu entry by default
if (!$menu_entry) {
    $menu_entry = first($menu_root->subentries);
}


$top_url = new Url("top.php");
$top_url->add_param('lg', $lg);
$top_url->add_param('display', $menu_entry->name);

$navig_url = new Url("navig.php");
$navig_url->add_param('lg', $lg);
$navig_url->add_param('display', $menu_entry->name);

$admin_url = new Url("admin.php");
$admin_url->add_param('lg', $lg);

$menu_action = $menu_entry->get_action();

if ($menu_action instanceof MenuLink) {
    $admin_url = $action->get_link();
} else {
    // Always display messages on backend load
    $actions = [ Action::make('message_load') ];

    // Add pending actions
    $queued = new ActionQueues(Action::request_actions($_REQUEST));
    $actions = array_merge($actions, $queued->displays());

    // Menu entry is added last so pending displays are shown first
    $actions []= $menu_action;

    foreach($actions as $action) {
        $admin_url->add_param($action);
    }
}

$smarty->assign('top_url', $top_url);
$smarty->assign('navig_url', $navig_url);
$smarty->assign('admin_url', $admin_url);
$smarty->assign('lg', $lg);
$smarty->display('frameset.tpl');


// Let daily jobs run after the rest of the script finished
require_once 'lib/Cron.php';
Cron::run_on_shutdown();

flush_exit();
