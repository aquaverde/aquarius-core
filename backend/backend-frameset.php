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
$action = $menu_entry->get_action();
if ($action) {
    if ($action instanceof MenuLink) {
        $admin_url = $action->get_link();
    } else {
        $admin_url->add_param(Action::make('message_load'));
        $admin_url->add_param($action);
    }
}

$smarty->assign('top_url', $top_url);
$smarty->assign('navig_url', $navig_url);
$smarty->assign('admin_url', $admin_url);
$smarty->assign('lg', $lg);
$smarty->display('frameset.tpl');


// Let daily jobs run after the rest of the script finished
require_once 'lib/cron.php';
Cron::run_on_shutdown();

flush_exit();
