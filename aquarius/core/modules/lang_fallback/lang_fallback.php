<?php
/** Switch to another language should the desired page not be avialable in the
  * selected language.
  *
  * This is useful on sites where not all content is available in all languages.
  * While the system takes care not to generate dead links, it may still happen
  * when content is deactivated, deleted, or with curious users. 
  * In such cases it makes sense to proffer the page in another language, if
  * there is one available. */
class Lang_Fallback extends Module {
    var $register_hooks = array('frontend_extend_node_detection');
    var $short = "primary_lg_fallback";
    var $name  = "Switch to another language when content is missing";


    /** Determine what site a node belongs to */
    function site_of_node($node) {
        foreach($this->site_roots() as $site_root) {
            if ($node->id == $site_root->id || $site_root->ancestor_of($node)) return $site_root;
        }
        return false;
    }


    /** Node detection is extended to check for content availability */
    function frontend_extend_node_detection($node_detection) {
        $node_detection->add_step('lang_fallback', array($this, 'lang_fallback_step'), 'before', 'use_current_node');
    }


    /** Check whether content is available or switch to primary language */
    function lang_fallback_step($params) {
        // Check that the page is available for the selected language.
        $lg = $params['lg'];
        $node = $params['current_node'];
        $content = $node->get_content($lg);

        if ($content && (!$params['require_active'] || $content->active)) {
            // We're good
        } else {
            // Log::warn("Suspected terrorist activity: User tried accessing deleted page $node->id in foreign language $lg. Investigate ".$_SERVER['REMOTE_ADDR']);
            Log::debug("Page unavailable in language $lg, trying to find content in another language");
            $all_content = $node->get_all_content();
            
            // First try to be sensitive about the user agent's configured languages, tamam mı?
            foreach(explode(",", get($params['server'], 'HTTP_ACCEPT_LANGUAGE')) as $user_language) {
                $user_lg = substr($user_language, 0, 2);
                foreach($all_content as $cancontent) {
                    if ($user_lg == $cancontent->lg) {
                        /* We do actually have content in one of the languages
                         * that the user agent says it accepts. REJOICE! CELEBRATE! REDIRECT! */
                        return array('lg' => $cancontent->lg);
                       
                    }
                }
            }
            
            // Ok, we'll just let you have content in the language that came up
            // first. Don't be mad, ok?
            $first_content = array_shift($all_content);
            if ($first_content) return array('lg' => $first_content->lg);
        }
    }
}
