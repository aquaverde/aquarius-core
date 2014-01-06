<?php
/** Backend navig frame
  * @package Aquarius.backend
  */

require '../lib/init.php';
require 'backend.php';

// We do not need sessions around here, unblock it.
session_write_close();

$display = requestvar('display', 'contents');

require_once 'lib/menu.php';

$menu = Menu::make($lg);

$display = requestvar('display');

// Display the inventory by default
$menu_entry = $menu->get_entry($display);
if (!$menu_entry)
    $menu_entry = $menu->get_entry('menu_inventory');

$smarty = $aquarius->get_smarty_backend_container();

// Build node tree for inventory, only if it's necessary
if ($menu_entry->name == 'menu_inventory') {
    require_once "lib/db/Node.php";
    $root = db_Node::get_root();

    $open_nodes = NodeTree::get_open_nodes('navig');
    array_unshift($open_nodes, $root->id);
    $tree = NodeTree::editable_tree($root, $lg, $open_nodes);
    NodeTree::add_controls($tree, $open_nodes, 'none', false, $lg);
    $tree['show_toggle'] = false; // Hack: do not show toggle for root node

    $smarty->assign('tree', $tree);
}

// Load fallback action, should be the same as is chosen when clicking on a menu entry in the top-frame
$fallback_action = $menu_entry->get_action();
$fallback_action->sequence = 0; // Ensure that the fallback is not executed before main action

// URL used for links to main frame
$adminurl = new Url('admin.php');
$adminurl->add_param('lg', $lg);
$adminurl->add_param($fallback_action);
$smarty->assign('adminurl', $adminurl);


$smarty->assign('lg', $lg);
$smarty->assign('url', $url);
$smarty->assign("defaultManagerSyle", DEFAULT_MANAGER_STYLE);
$smarty->assign('menu', $menu_entry);
$smarty->assign('display', $display);
$smarty->assign('revision', $aquarius->revision());
$smarty->display('navig.tpl');