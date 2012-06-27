<?
class Newsletter extends Module {

    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend');
    
    var $short = "newsletter";
    var $name  = "Newsletter Modul";
    
    function need_install() {
        global $DB;
        $query = "SHOW TABLES";
        $result = $DB->query($query);
        $tables = array();
        while($values = mysql_fetch_row($result)) {
            $tables[] = $values[0];
        }
        if(!in_array('newsletter_addresses',$tables))
            return true;
        if(!in_array('newsletter_subscription',$tables))
            return true;
        return false;
    }


    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_modules',
            200,
            new Menu('newsletter_menu', False
        ));
        $menu->add_entry(
            'newsletter_menu', 
            1,
            new Menu('newsletter_edit', Action::make('newsletter_edit', NEWSLETTER_ROOT_NODE, $lg)
        ));
        $menu->add_entry(
            'newsletter_menu', 
            2,
            new Menu('newsletter_addresses', Action::make('newsletter','listaddresses', NEWSLETTER_ROOT_NODE, $lg)
        ));
        $menu->add_entry(
            'newsletter_menu', 
            3,
            new Menu('newsletter_send', Action::make('newsletter','sendnewsletter', NEWSLETTER_ROOT_NODE, $lg)
        ));
        $menu->add_entry(
            'newsletter_menu', 
            4,
            new Menu('newsletter_subscriptions_cleanup', Action::make('newsletter','cleanuplist', NEWSLETTER_ROOT_NODE, $lg)
        ));
    }


    function get_mail_transport() {
        require_once('Mail.php');
        $transport_params = $this->conf('transport');
        if (empty($transport_params)) {
            $transport_params = array(
                'type'     => 'mail',
                'max_rcpt' => 10
            );
        }

        $transport = Mail::factory($transport_params['type'], $transport_params);

        $max_rcpt = get($transport_params, 'max_rcpt', 10);
        $from     = get($transport_params, 'from');
        $delay_per_rcpt = get($transport_params, 'delay_per_rcpt', 0);
        return new Mail_Transport_Chunked($transport, $max_rcpt, $from, $delay_per_rcpt);
    }
}
?>