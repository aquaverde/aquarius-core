<?php

/** Represents an URL
  */
class Url {
    var $scheme;
    var $host;
    var $port;
    var $path;
    var $params;
    var $anchor;

    /** Parse an URL string
      * Assumes '&' as query string parameter separator
      */
    static function parse($url_string) {
        $components = parse_url($url_string);
        $url = new self(get($components, 'path'));
        $url->scheme = get($components, 'scheme');
        $url->host = strtolower(get($components, 'host'));
        $url->port = get($components, 'port');
        $url->anchor = get($components, 'fragment');

        $params = explode('&', get($components, 'query', ''));
        foreach($params as $param) {
            @list($key, $value) = explode('=', $param, 2);
            $key = urldecode($key);
            if (strlen($value) > 0) {
                $url->add_param($key, urldecode($value));
            } elseif (strlen($key) > 0) {
                $url->add_param($key);
            }
        }
        return $url;
    }

    static function of_request() {
        $url = self::parse($_SERVER['REQUEST_URI']);
        $url->host = $_SERVER['HTTP_HOST'];
        return $url;
    }

    function __construct($path='', $params=array(), $anchor=false) {
        $this->path = $path;
        $this->params = $params;
        $this->anchor = $anchor;
    }

    function str($escape=false) {
        $urlstr = "";
        if ($this->host) {
            // If we have to hostname we should also have the protocol
            $scheme = $this->scheme;
            if (!$scheme) {
                // We assume that the host we link to uses the same scheme we use
                // This is likely to be wrong in many cases.
                $scheme = URL_SCHEME;
            }
            $urlstr .= $scheme.'://'.$this->host;
        }
        if ($this->path)
            $urlstr .= $this->path;
        if (count($this->params) > 0) {
            $ps = array();
            foreach($this->params as $name=>$value) {
                $name = urlencode($name);
                if (strlen($value) > 0)
                    $ps[] = "$name=".urlencode($value);
                else
                    $ps[] = "$name";
            }
            $urlstr .= "?".join("&", $ps);
        }
        if (strlen($this->anchor) > 0)
            $urlstr .= '#'.urlencode($this->anchor);

        if ($escape) $urlstr = htmlspecialchars($urlstr);

        return $urlstr;
    }

    function __toString() {
        return $this->str(true);
    }

    /** Add a request parameter
      * @param $name
      * @param $value optional
      * Parameters with same name will be replaced
      */
    function add_param($name, $value=false) {
        $this->params[str($name)] = str($value);
        return $this;
    }

    /** Copy URL and add param */
    function with_param($name, $value=false) {
        $url = clone $this;
        return $url->add_param($name, $value);
    }
    
    /** Add parameters from given dict */
    function add_params($params) {
        foreach($params as $name => $value) {
            $this->add_param($name, $value);
        }
    }

    /** Produce a copy of this URL relative to another
      * Relativizes URL by removing parts it has in common with reference URL
      * @param reference_url the base URL
      * @return new URL from this URL, relative to reference_url
      * If this URL has no host part, it is assumed to be equal to the reference_url host part. */
    function relative_to($reference_url) {
        $url = clone $this;
        $same_host = $reference_url->host == $url->host;
        if ($same_host) {
            $url->host = null;
        }
        return $url;
    }
}


