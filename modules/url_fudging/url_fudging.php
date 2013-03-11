<?php 
/** Tools to preprocess frontend URL
  *
  * As the name suggests this module is intended for temporary measures. It
  * works best in combination with uri correction enabled, as this gives
  * user-agents feedback about the correct URL.
  *
  * This is nothing but doing preg_replace() on the request URL.
  *
  * Example config:
  * $config['url_fudging']['short description'] = array(
  *     'pattern' => '%/problems/%', // percent sign used as regexp pattern delimiter
  *     'replacement' => '/panem%20et%20circenses/'
  * );
  * This rule would cause the module to modify URL like this:
  *     http://news.example/categories/problems/those_kids.html
  * would be replaced with
  *     http://news.example/categories/panem%20et%20circenses/those_kids.html
  * and content for this URL would be displayed.
  */
class url_fudging extends Module {
    var $short = "url_fudging";

    var $register_hooks = array('frontend_extend_node_detection') ;

    /** Add the fudging as first step before other steps, so that later steps
      * see the modified URI */
    function frontend_extend_node_detection($node_detection) {
        // Add our fudging step in front of all other steps
        $node_detection->add_step('uri_fudging', array($this, 'fudge_this'), 'before');
    }
    
    /** Fudges the 'uri' object in the params */
    function fudge_this($params) {
        $uristr = $params['uri'];
        $changed = false;
        foreach($this->conf('', array()) as $fudge_name => $fudge_config) {
            $new_uristr = preg_replace($fudge_config['pattern'], $fudge_config['replacement'], $uristr);
            if ($new_uristr === null) throw new Exception("preg_replace() choked on $fudge_name");
            if ($new_uristr != $uristr) {
                Log::debug("uri_fudge $fudge_name changed '$uristr' to '$new_uristr'");
                $uristr = $new_uristr;
                $changed = true;
            }
        }
        if ($changed) return array('uri' => Url::parse($uristr));
    }
}