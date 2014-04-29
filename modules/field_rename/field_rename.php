<?php 
class Field_rename extends Module {
        
    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend');
    
    var $short = "field_rename";
    var $name  = "Rename content fields";
    
    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_super_tools',
            false,
            new Menu(false, Action::make('field_rename', 'show')
        ));
    }
}
