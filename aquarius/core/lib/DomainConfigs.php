<?
/** Keep configuration parameters for domains
  * Subdomains inherit configuration parameters from their superdomain. Example:
  *
  *   $domainconf = new DomainConfigs();
  *   $domainconf->add(          'example.com', array('lg' => 'en'));
  *   $domainconf->add(   'search.example.com', array('node' => 'search'));
  *   $domainconf->add(          'example.fr',  array('lg' => 'fr'));
  *   $domainconf->add('recherche.example.fr',  array('node' => 'search'));
  *
  * Now
  *
  *   $domainconf->get('search.example.com');
  *
  * Will return
  *
  *   array('lg' => 'en', 'node' => 'search');
  *
  * This implementation is totally not optimized for speed. But it should be fairly easy to add indexes and caches.
  *
  */
class DomainConfigs {
    // Config parameters per domain name
    // Domain names are stored with a trailing dot representing the root domain
    private $confs = array();


    /** Create domain configurations structutre and add given configs */
    function __construct($domain_confs) {
        foreach($domain_confs as $domain => $confs) $this->add($domain, $confs);
    }


    /** Add or replace a configuration option for a domain
      * @param $domain store config values for this domain name and all subdomains
      * @param $new_confs config parameter dictionary to merge with already existing entries (new overwrite old)
      * $domain may be the empty string, in this case the given confs apply to all domains.
      */
    function add($domain, $new_confs) {
        $existing_confs = get($this->confs, $domain, array());
        $this->confs[$domain] = array_merge($existing_confs, $new_confs);
    }


    /** Get config values for a domain name
      * @param $domain retrieve config values for this domain and those of its parent domains.
      * @param $name Lookup this parameter in the config and return its value instead of the config dict
      * @return config dictionary for this domain. Config entries of superdomains are returned as well.
      */
    function get($domain, $name = false) {
        $confs = get($this->confs, $domain, array());
        if (strlen($domain) > 0) {
            $superdomain = $domain;
            $next_dot = strpos($superdomain, '.');
            if ($next_dot === null) $superdomain = '';
            else $superdomain = substr($superdomain, $next_dot+1);
            $confs = array_merge($this->get($superdomain), $confs);
        }
        if ($name) {
            return get($confs, $name);
        } else {
            return $confs;
        }
    }

    /** Out of the configured domains, find those configs that have a certain parameter
      * @param $name name of the parameter that must exist
      * @param $value optionally require the parameter to have this value, ignored if null */
    function lookup($name, $value = null) {
        $found = array();
        foreach($this->confs as $domain => $_) {
            $confs = $this->get($domain);
            if (isset($confs[$name])) {
                if ($value !== null) {
                    if ($confs[$name] == $value) {
                        $found[$domain] = $confs;
                    }
                } else {
                    $found[$domain] = $confs;
                }
            }
        }
        return $found;
    }
}
?>