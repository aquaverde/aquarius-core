<?php
/**
* 
*/
class sqlInput extends Module
{
    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend');
    
    var $short = "sqlInput";
    var $name  = "Sql Input Modul";
    
    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_modules',
            300,
            new Menu('sqlInput_menu', false, false, array(
            	1 => new Menu('sql_input', Action::make('sqlInput','showInput', $lg))
            ))
       	);
    }
}

