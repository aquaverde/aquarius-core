<?php 
/** Redirections based on keywords in URI
  * 
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
  * 
  *  */
class Shorturi extends Module {
    var $register_hooks = array('menu_init', 'smarty_config', 'smarty_config_backend', 'smarty_config_frontend', 'frontend_extend_node_detection', 'init_form');
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


    function init_form($formtypes) {
        // Add the shorturi field
        $formtypes->add_formtype(new Formtype_Urltitle('shorturi', 'urltitle'));
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
                $domain = preg_replace('%^www\\.%i', '', $params['uri']->host);
                Log::debug("Looking for shortcut name '$shortcut' for domain '$domain'.");

                global $aquarius;
                $matching_content = $aquarius->db->listquery("
                    SELECT DISTINCT content.id
                    FROM node
                    JOIN content ON node.id = content.node_id
                    JOIN content_field ON content.id = content_field.content_id
                    JOIN content_field_value ON content_field.id = content_field_value.content_field_id
                    JOIN form ON node.form_id = form.id
                    JOIN form_field ON form.id = form_field.form_id
                    WHERE form_field.type = 'shorturi' 
                    AND form_field.name = content_field.name
                    AND content_field_value.value COLLATE utf8_general_ci = ?
                ", array($shortcut));

                if ($matching_content) {
                    $best = false;
                    $best_score = -1;
                    foreach($matching_content as $content_id) {
                        $content = DB_DataObject::factory('content');
                        $loaded = $content->get($content_id);
                        if (!$loaded) continue; // it did WHAT?

                        $score = 0;
                        if ($content->lg == $params['lg']) $score += 1;
                        if ($best_score < $score) {
                            $best = $content;
                            $best_score = $score;
                        }
                    }
                    if ($best) {
                        $params['current_node'] = $best->get_node();
                        $params['lg'] = $best->lg;
                        return $params;
                    }
                }

                $shorturi_search            = DB_DataObject::factory("shorturi");
                $shorturi_search->domain    = $domain;
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
