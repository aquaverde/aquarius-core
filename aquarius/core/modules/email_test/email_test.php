<?
class Email_Test extends Module {
        
    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend');
    
    var $short = "email_test";
    var $name  = "Test email sending";
    
    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_super_tools',
            false,
            new Menu(false, Action::make('email_test', 'form')
        ));
    }
}
?>
