<?php 
class Port extends Module {

    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend');

    var $short = 'port';
    var $name  = 'Port content between sites';

    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_super_tools',
            false,
            new Menu(false, Action::make('port_dialog')
        ));
    }
}
