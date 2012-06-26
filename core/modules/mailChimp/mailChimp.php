<?php
/**
* 
*/
class mailChimp extends Module
{
    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend');
    
    var $short = "mailChimp";
    var $name  = "MailChimp";
    
    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_modules',
            300,
            new Menu('mailChimp_menu', false, false, array(
            	1 => new Menu('mailChimp_upload', Action::make('mailChimp','upload','', false, $lg))
            ))
       	);
    }	
}

?>