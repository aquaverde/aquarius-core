<?
class Tableexport extends Module {

    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend');
    var $short = "tableexport";
    var $name  = "Tableexport Modul";
    
    function menu_init($menu, $lg) {
      //  require_once('lib/db/Wording.php');
        
        $menu->add_entry(
            'menu_modules',
            30,
            new Menu('tableexport_menu', 
                Action::make('tableexport', 'view1','null')
                 
        ));
        $menu->add_entry(
            'tableexport_menu',
            10,
            new Menu('tableexport_view', 
                Action::make('tableexport', 'view','null')
        ));
        $menu->add_entry(
            'tableexport_menu',
            20,
            new Menu('tableexport_export', 
                Action::make('tableexport', 'export','null')
        ));
        $menu->add_entry(
            'tableexport_menu',
            30,
            new Menu('tableexport_deleteall',
                Action::make('tableexport', 'deleteallconfirm','null')
        ));
    }
}
?>