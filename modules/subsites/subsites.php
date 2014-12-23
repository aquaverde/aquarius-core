<?php 
/** Keep multiple sites in one Aquarius DB with this module
  * Aquarius frontend behaviour is extended in the following ways:
  * - do not include subsite name in url path
  * - add domain when crossing subsite boundaries
  * - start from subsite root when interpreting request URL
  * - provide {subsite_search} plugin
  * - assign the subsite root content to the smarty variable sub_root for frontend templates
  *
  * It ensures that URLs do not show the subsite as part of the URL. So instead of an URL like this:
  *    /subsite/articles/checkthisout.523.html
  * it produces
  *    /articles/checkthisout.523.html
  * This wouldn't work on links crossing subsite borders. Links leading from one subsite to another include a domain in those cases:
  *    http://subsite.example.com/articles/checkthisout.523.html
  * 
  * An additional pointing formfield "Pointing_Subsite" is provided that offers node selection within the boundaries of the subsite
  *
  *  */
class Subsites extends Module {
    var $register_hooks = array('init_form', 'smarty_config_backend', 'frontend_extend_node_detection', 'frontend_extend_uri_factory', 'frontend_page') ;
    var $short = "subsites" ;
    var $name  = "Manage URL for subsites" ;

    var $site_roots;
    var $domain_conf;

    function initialize($aquarius) {
        parent::initialize($aquarius);

        // Keep a reference to the domain configuration
        $this->domain_conf = $aquarius->domain_conf;

        // Load subsite roots
        $debug_site_names = array("Detected subsites:");
        foreach($this->conf('sites') as $name => $site_conf) {
            $sitenode = db_Node::get_node($name);
            if (!$sitenode) throw new Exception("Subsites: unable to load subsite root node '$name'");
            $this->site_roots[$name] = $sitenode;
            $debug_site_names []= $name.": ".$sitenode->idstr();
        }
        Log::debug(join("\n    ", $debug_site_names));
    }

    function init_form($formtypes) {
        $formtypes->add_formtype(new Formtype_Pointing_Subsite('pointing_subsite', 'pointing', $this));
    }

    /** Determine what site a node belongs to */
    function site_of_node($node) {
        foreach($this->site_roots() as $site_root) {
            if ($node->id == $site_root->id || $site_root->ancestor_of($node)) return $site_root;
        }
        return false;
    }

    /** Find the subsite node for given domain */
    function site_of_domain($domain) {
        $site_root = $this->domain_conf->get($domain, 'subsite');
        return db_Node::get_node($site_root);
    }

    /** Array of subsite root nodes */
    function site_roots() {
        return $this->site_roots;
    }

    /** Get config for subsite identified by root id */
    function site_config($root) {
        $rootnode = db_Node::get_node($root);
        if (!$rootnode) throw new Exception("Subsites: unable to load subsite root node '$site_root'");

        $config = get($this->site_confs, $rootnode->id);
        if (!$config) throw new Exception("No config for subsite root ".$rootnode->idstr().". Not a subsite root?");

        return $config;
    }

    /** Adds subsite specific variables
      *
      * Checks whether subsite host name should be used in URI.
      * Users should be able to work in the backend using a neutral domain, and click around on the frontend without changing domain. (When the domain changes, they are no longer logged in.)
      *
      * Also assigns the subsite root to the sub_root variable.  */
    function frontend_page($smarty, $node, $detection_params) {
        // Ugly hack to not change host when backend user is logged in
        $use_subsite_host = true;
        if (db_Users::authenticated()) {
            // Only use subsite hosts when backend user is already using one
            $use_subsite_host = get($detection_params, 'using_subsite_host');
        }
        $smarty->uri->use_subsite_host = $use_subsite_host;

        $subsite = $this->site_of_node($node);
        if ($subsite) {
            $subsite_content = $subsite->get_content($smarty->get_template_vars('lg'));
            $subsite_content->load_fields();
            $smarty->assign('sub_root', $subsite_content);
        }
    }




/* Node detection changes */

    /** Node detection is extended to start from the subsite root node instead of the real root if the URL hostname belongs to a subdomain. */
    function frontend_extend_node_detection($node_detection) {
        $node_detection->add_step('subsite_by_domain', array($this, 'subsite_by_domain'), 'after', 'root_as_current_node');
        $node_detection->add_step('subsite_by_current_node', array($this, 'subsite_by_current_node'), 'before', 'use_current_node');
    }

    /** Node detection step: Try to determine the subsite based on the domain name
      * Sets the 'subsite' parameter if subsite is found. Also sets current_node
      * to the subsite root so that urlpath detection starts from there. */
    function subsite_by_domain($params) {
        $host = $params['uri']->host;
        $subsite = $this->site_of_domain($host);
        if ($subsite) {
            return array('subsite' => $subsite, 'current_node' => $subsite, 'using_subsite_host' => true);
        }
    }

    /** Sets the subsite paramater based on the current_node */
    function subsite_by_current_node($params) {
        return array('subsite' => $this->site_of_node($params['current_node']));
    }


/* URI construction changes */

    function frontend_extend_uri_factory($url_factory) {
        $url_factory->add_step('use_subsite_host',    array($this, 'use_subsite_host'), 'after', 'use_option_host');
        $url_factory->add_step('remove_subsite_parts', array($this, 'remove_subsite_parts'), 'after', 'path_parts_from_nodes');
    }

    /** Use hostname of subsite instead of current host
      * Also sets the subsite option to the detected subsite
      * This is activated only if the option use_subsite_host is set to true and a subsite host can be found. The option using_subsite_host will be set if a subsite host is used. */
    function use_subsite_host($options, $uri) {
        $options->using_subsite_host = false;
        if (!empty($options->use_subsite_host)) {
            $current_subsite = $this->site_of_node($options->target_node);

            $options->subsite = $current_subsite;
            if (!$current_subsite) return;

            $configs = $options->domain_conf->lookup('subsite', $current_subsite->name);

            // Now we have some domain names that are used for this subsite, which one is best to use?
            //   1. designated as 'main'
            //   2. has same language configured
            $scored_domains = array();
            foreach($configs as $domain => $confs) {
                $score = 0;
                if (get($confs, 'main'))               $score++;
                if (get($confs, 'lg') == $options->lg) $score++;
                $scored_domains[$score] = $domain; // When multiple domains have the same score, we just pick one
            }
            krsort($scored_domains); // Highest score first
            if (!empty($scored_domains)) {
                $uri->host = first($scored_domains);
                $options->using_subsite_host = true;
            }
        }
    }

    /** Remove subsite parts in URI path so that only parts from the subsite to its children remain */
    function remove_subsite_parts($options, $uri) {
        if ($options->using_subsite_host) {
            $current_subsite = $this->site_of_node($options->target_node);
            if (!$current_subsite) return;

            // Just remove path parts before the subsite root
            $options->path_parts = array_slice($options->path_parts, $current_subsite->depth());
        }
    }
}
