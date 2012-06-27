<?
/** Make a reference to a node.
  * Parameters:
  *     node: Node to be referenced, can be a node object or a node id
  *     lg: Language of the target reference. Default is the current language.
  *     varparams: Comma separated list or array of smarty variables to pass in URL (escaped, of course)
  *     param.*: The values of parameters whose names start with "param" will be appended as parameters to the url (parameter values will be escaped), instead of just one parameter, a dict may be passed, its content will be added as parameters
  *     carry_params: Optional parameter to include current GET parameters in link (does not carry language, lg)
  *     escape: whether returned URL should be escaped (&amp;), default true
                if this is the string 'url', urlencode() is used
  *     var: assign URL to var
  *
  * If there's no content for the desired language, this function returns a reference to the next parent that has content in this language.
  *
  * Example:
  *   {assign var="search" value="mein suchtext"}
  *   {assign var="next" value="20"}
  *   {href node=$searchnode varparams="search,next" param1="length=20" param2="sure"}
  * yields something like
  *   /de/search/?search=mein+suchtext&next=20&length=20&sure
  *
  */

function smarty_function_href($params, $smarty) {  
    $node = db_Node::get_node(get($params, 'node'));
    if (!$node) $smarty->trigger_error("href: node could not be loaded");

    $lg = get($params, 'lg', $smarty->get_template_vars('lg'));

    $url = $smarty->uri->with('lg', $lg)->to($node);

    if (get($params, 'carry_params', false)) {
        foreach($_GET as $key => $value) {
            if ($key != 'lg') $url->add_param($key, $value);
        }
    }

    // Add list of smarty vars
    $varparams = get($params, 'varparams');
    if ($varparams) {
        if (!is_array($varparams)) $varparams = explode(',', $varparams);
        foreach($varparams as $var) {
            $value = $smarty->get_template_vars(trim($var));
            $url->add_param($var, $value);
        }
    }

    // Look for string parameters to be added
    foreach($params as $name=>$val) {
        if (preg_match("/^param/", $name)) {
            if (is_array($val)) $url->add_params($val);
            else {
                // UBW, the params may be of the form 'name=value' in which case we don't want the '=' to be escaped by the URL class. However, 'param' and 'value' should be escaped.
                @list($key, $value) = split('=', $val, 2);
                $url->add_param($key, $value);
            }
        }
    }

    $urlstr = $url->str(false);
    $escape = get($params, 'escape', true);
    if ($escape === 'url') {
        $urlstr = urlencode($urlstr);
    } elseif($escape) {
        $urlstr = htmlentities($urlstr);
    }
    if (isset($params['var'])) {
        $smarty->assign($params['var'], $urlstr);
    } else {
        return $urlstr;
    }
}
?>