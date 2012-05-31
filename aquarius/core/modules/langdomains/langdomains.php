<?
/** Use one domain per language
  * This module is geared towards sites that are served under different host names based on language.
  * It ensures that the correct domain for each language is used. The config
  * variable frontend/domains is used to discover the hostname for each language. */
class Langdomains extends Module {

    var $register_hooks = array('frontend_extend_uri_factory');

    var $short = "langdomains";
    var $name  = "Change domain based on language";

    var $lang_domains = array();

    function initialize($aquarius) {
        parent::initialize($aquarius);
        foreach($aquarius->conf('frontend/domains', array()) as $domain=>$conf) {
            $lg = get($conf, 'lg');
            if ($lg) {
                $this->lang_domains[$lg]  = $domain;
            }
        }
    }

    function frontend_extend_uri_factory($url_factory) {
        $url_factory->add_step('langdomain_host_by_lg', array($this, 'langdomain_host_by_lg'), 'after', 'use_option_host');
    }

    /** Use the configured domain for each subsite */
    function langdomain_host_by_lg($options, $uri) {
        $language_domain = get($this->lang_domains, $options->lg);
        if ($language_domain) {
            $uri->host = $language_domain;
        }
    }
}
?>