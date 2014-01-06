<?php 
/** Redirections based on keywords in URI
  * Example:
  *
  * $config['shorturi']['redirections'] = array(
  *    'shiny'       => 'http://www.my.example/products/shiny/extra.294',
  *    'special'     => 'http://www.my.example/supi-offer/checkthisout',
  *    'leave'        => 'http://www.altavista.com/web/results?q=home',
  * );
  *
  * In this example 'http://www.my.example/en/shiny' would be redirected to
  * 'http://www.my.example/fr/products/shiny/extra.294' unless there is an
  * urltitle that matches. Leaving out the language code (as in
  * 'http://www.my.example/shiny') would work in theory, but in practice
  * due to the current .htaccess setup only requests containing the language
  * code are received.
  *  */
class Shorturi extends Module {
    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend', 'frontend_extend_node_detection');
    var $short          = "shorturi" ;
    var $name           = "URI shortcut redirection" ;


    function menu_init($menu, $lg) {
        $menu->add_entry(
            'menu_modules',
            false,
            new Menu('shorturi_menu', false, false, array(
              1 => new Menu('shorturi_manage', Action::make('Shorturi','manage', $lg))
            ))
        );
    }


    /** Node detection is changed to look for shortcuts in the URI */
    function frontend_extend_node_detection($node_detection) {
        // Replace current 'urltitle' step
        // The shorturi title detections step waraps around the replaced step and comes into action when the wrapped step failed.
        $original_urltitle_step = $node_detection->get_step('urltitle');
        if (!$original_urltitle_step) throw new Exception("Missing 'urltitle' step, can't replace it");
        
        $detector = new Shorturi_Detection_Step($this->conf('redirections'), $original_urltitle_step);
        $node_detection->add_step('urltitle', array($detector, 'detect_shortcuts'), 'after', 'urltitle');
    }

}

class Shorturi_Detection_Step {
    var $redirections;
    var $original_urltitle_step;
    
    function __construct($redirections, $original_urltitle_step) {
        $this->redirections  = $redirections;
        $this->original_urltitle_step = $original_urltitle_step;
    }

    /** Intercept 'notfound' message of wrapped 'urltitle' step and look whether there's a matching redirection.  */
    function detect_shortcuts($params) {
        // First try original 'urltitle' step
        $modified_params = call_user_func($this->original_urltitle_step, $params);

        // When 'urltitle' said 'notfound', look in the shorturi config
        if (get($modified_params, 'notfound')) {
            $path_parts = $params['path_parts'];
            Log::debug('Urltitle says "notfound", checking shorturi redirection');
            // Attempt detection only when there's exactly one path part
            if (count($path_parts) == 1) {

                $shortcut = first($path_parts);
                Log::debug("Looking for shortcut name '$shortcut'");
                
                $shorturi_search            = DB_DataObject::factory("shorturi");
                $shorturi_search->domain    = substr($params['uri']->host,4);
                $shorturi_search->keyword   = mb_strtolower($shortcut);

                if($shorturi_search->find(true))
                {
                    return array('redirect' => $shorturi_search->redirect);
                }

                $shorturi_search            = DB_DataObject::factory("shorturi");                
                $shorturi_search->keyword   = mb_strtolower($shortcut);
                $shorturi_search->whereAdd("`domain` = ''");

                if($shorturi_search->find(true))
                {
                    return array('redirect' => $shorturi_search->redirect);
                }

                // first check domain configs
                $redirections_per_domain = $params['domain_conf']->get($params['uri']->host, 'shorturi');
                $redir_uri = get($redirections_per_domain, $shortcut);
                
                // then check module config
                if (!$redir_uri) {
                    $redir_uri = get($this->redirections, $shortcut, null);
                }
                
                if ($redir_uri) {
                    return array('redirect' => $redir_uri);
                } else {
                    Log::debug("No shortcut '$shortcut' found.");
                }
            }
        }
        return $modified_params;
    }
}
?>