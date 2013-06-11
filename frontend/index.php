<?php
/** Frontend dispatcher code.
  * @package Aquarius.frontend
  *
  * Finds content for a given URL and displays page.
  *
  * The following steps are preformed:
  * <ol>
  *   <li>Determine the language to use</li>
  *   <li>Determine the node referenced by the URL</li>
  *   <li>Check node restrictions and authorization of user</li>
  *   <li>Find the appropriate template</li>
  *   <li>Collect all necessary vars in a smarty container and display the template</li>
  * </ol>
  *
  * <h2>In detail</h2>
  * Each frontend request maps to a node. The first task is to discover which node was requested. There are basically two ways to address a node:
  * <ol>
  *   <li>
  *      Directly with a node id and language:
  *      <code>http://www.aquarius.example/index.php?lg=fr&id=123</code>
  *   </li>
  *   <li>
  *      Through <a href="http://httpd.apache.org/docs/2.0/rewrite/rewrite_guide.html">URL rewriting</a>:
  *      <code>http://www.aquarius.example/de/apples/and/pears.123.html</code>
  *   </li>
  * </ol>
  * In the first case, the node to be used is simply the one given by the id parameter. This is easy.
  * When using URL rewriting, however, finding the appropriate node is trickier. The path in the URL contains elements wich are either urltitles ('apples' in the above example) or titles with ID attached ('pears.123'). The '.html' suffix is discarded.
  *
  * When the requested node has been determined, we make sure it is intended for display by checking its active flag.
  *
  * Some nodes do not have their own page but are merely used for structuring. In such cases the form of the node may give a 'fall through' option. In this case we will fall through to the first available child of the node and repeat as necessary.
  * 
  * Having a node with content in the desired language, we can read the template name to be used from the node's form.
  *
  * Nodes may be access restricted, which means that they themselves or one of their parents has the access_restricted flag set. If this is the case, there must be a registered frontend user with sufficient credentials to access the node, otherwise the login.tpl is used instead of the template determined for the node.
  *
  * There are two special template names:
  *  login.tpl: User Agent does not have enough credentials to access this URL
  *  not_found.tpl: For invalid URL, URL leading to inactive node or node not having content
  *
  * Having determined the template name, as well as node and content, a smarty container is assigned the following variables:
  *  <dl>
  *    <dt>lg</dt>      <dd>language code</dd>
  *    <dt>node</dt>    <dd>Node instance</dd>
  *    <dt>content</dt> <dd>Content instance</dd>
  *    <dt>form</dt>    <dd>Form instance </dd>
  *    <dt>login_state</dt><dd>Result of login attempts</dd>
  *    <dt>user</dt>    <dd>Fe_User instance if one is logged in</dd>
  *    <dt>admin_user</dt>    <dd>User instance if a user is logged into the backend</dd>
  *    <dt>restriction_node</dt><dd>Only when diverted to login template: Node that has access_restriction flag set (may be the same as $node or a parent of $node)</dd>
  *    <dt>template</dt><dd>Name of the template</dd>
  *  </dl>
  * Also, all fields of the content are assigned directly. So a typical template can expect to see the variables $title and $text.
  *
  * The smarty caching facility is used to cache pages. Pages with identical request variables are served from the smarty cache. Only GET requests are cached, and caching is disabled for users logged into the backend.
  */

require '../lib/init.php';
  
try {
    // Start session if needed
    $aquarius->session_start($aquarius->conf('frontend/use_session', false));

    $aquarius->execute_hooks('frontend_init');

    $request_uri = Url::of_request();

    $moved_permanently_url = $aquarius->domain_conf->get($request_uri->host, 'moved_permanently');
    if ($moved_permanently_url) {
        header("HTTP/1.1 301 Moved to new domain");
        header("Location: $moved_permanently_url");
        flush_exit();
    }

    $root_node = db_Node::get_root();

    $language_detection = new Language_Detection(array(
        'request_parameter',
        'request_path',
        'domain',
        'accepted_languages',
        'primary'
    ));
    $aquarius->execute_hooks('frontend_extend_language_detection', $language_detection);

    $detection_params = array(
        'request' => clean_magic($_REQUEST),
        'server' => $_SERVER,
        'uri' => $request_uri,
        'domain_conf' => $aquarius->domain_conf
    );

    $lg = $language_detection->detect($detection_params);
    assert('strlen($lg) == 2');

    $detection_params['lg'] = $lg;
    
    // Usually, display node only if it is active
    $require_active = true;

    // Allow display of inactive content for preview
    $preview = requestvar('preview');
    $carry_preview = false;
    if ($preview && $preview == preview_hash(ECHOKEY)) {
        $require_active = false;
        $carry_preview = $preview;
        Log::debug('Enabling preview of disabled content');
    }
    $detection_params['require_active'] = $lg;

    // Process logout requests
    if (isset($_REQUEST['logout'])) {
        db_Fe_users::logout();
        $aquarius->execute_hooks('frontend_logout');
    }

    // Process logins
    $login_state = db_Fe_users::authenticate();
    if ($login_state instanceof db_fe_users) {
        $aquarius->execute_hooks('frontend_login', $login_state);
    }

/* Find node from URI */
    $node_detection = new Node_Detection(array(
        'root_as_current_node',
        'path_parts_from_uri',
        'non_rewritten_node_id',
        'remove_lg_prefix',
        'remove_suffix',
        'use_domain_preset',
        'node_id_in_path',
        'urltitle',
        'fallthrough',
        'use_current_node'
    ));
    $aquarius->execute_hooks('frontend_extend_node_detection', $node_detection);

    $node_params = $node_detection->process($detection_params);
    $lg = $node_params['lg'];
    assert('strlen($lg) == 2');
    
    header('Content-Language: '.$lg);

    // Q&D locale hack
    $long_lg = $lg.'_'.strtoupper($lg).'.UTF-8';
    setlocale(LC_TIME, $long_lg, $lg);

    // Instead of rewriting this part we currently keep using NotFoundException even though it's not needed anymore.
    class NotFoundException extends Exception {}
    try {
        if (isset($node_params['notfound'])) {
            throw new NotFoundException($node_params['notfound']);
        }

        if (isset($node_params['redirect'])) {
            header('HTTP/1.1 301 Shortcut redirection');
            header('Location: '.$node_params['redirect']);
            exit();
        }

        $node = $node_params['found'];

    /* Ensure we have a node fit for display */

        // Check that node is active
        if ($require_active && !$node->active()) {
            throw new NotFoundException("Refusing to display inactive node ".$node->idstr());
        }

        // We have a node now, but not necessarly one with content for the selected language.
        if (!$node->get_content($lg, $require_active)) {
            throw new NotFoundException("No content in language '$lg' for node ".$node->idstr());
        }

        Log::debug('Arrived at '.$node->idstr());

        // Tell interested parties about the chosen node and let them choose a different one
        $jump_nodes = $aquarius->execute_hooks('frontend_node', $node, $node_params);
        if (!empty($jump_nodes)) {
            $node = first($jump_nodes); // There is of course the problem that we could have requests to jump to two different nodes. For now we just choose the first node.
            Log::debug("Jumping to node ".$node->idstr());
        }
    } catch (NotFoundException $error) {
        header("HTTP/1.1 404 Not found");

        Log::debug($error);
        
        $node = $node_params['current_node'];
        if (!$node) $node = $root_node; // In case node detection goes worse than wrong

        $smarty = $aquarius->get_smarty_frontend_container($lg, $node);
        $aquarius->execute_hooks('frontend_page', $smarty, $node, $node_params);
        $smarty->display('not_found.tpl');
        flush_exit();
    }

/* Prepare smarty container*/
    $smarty = $aquarius->get_smarty_frontend_container($lg, $node);
    
    $cache_settings = $aquarius->conf('frontend/cache');
    $smarty->caching = (bool)$cache_settings['templates'];
    $smarty->cache_lifetime = intval($cache_settings['lifetime']);
    // maybe force smarty to recompile tepmlates on every execution.
    $smarty->force_compile = !(bool)$cache_settings['compiles'];

    // MONKEY CHANGE: add rogue property to container, currently read by loadnodes plugin
    // FIXME: Need to find a better way
    $smarty->require_active = $require_active;

    if ($carry_preview) {
        $smarty->uri->template_uri->add_param('preview', $carry_preview);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $smarty->caching = 0;
    }
    
    

    // Infom about the page that will be displayed, give a chance to select the template
    $template_requests = $aquarius->execute_hooks('frontend_page', $smarty, $node, $node_params);

    // Determine the template to use
    $template = 'not_found.tpl';
    if (!empty($template_requests)) {
        if (count($template_requests) > 1) Log::warn("Requests to switch to multiple templates (".join(' and ', $template_requests).") picking first");
        $template = first($template_requests);
    } else {
        $form = $node->get_form();
        if ($form) $template = $form->template;
    }


/* Maybe allow direct edit */
    // Allow only when client logged-in to backend
    require_once("db/Users.php");
    $admin_user = db_Users::authenticated();
    $smarty->assign('admin_user', $admin_user);
    $direct_edit = (bool)$admin_user;
    if($direct_edit) {
        // Use admin domain name so that user stays logged  in in the frontend
        $admin_domain = $aquarius->conf('admin/domain');
        if ($admin_domain) $smarty->uri->host = $admin_domain;
    
        // Because the pages render differently when direct_edit is enabled (they contain direct-edit links) we must disable smarty caching for those users.
        $smarty->caching = 0;
        Log::debug("Direct-edit enabled, smarty caching disabled");
    }


/* Check that the URL is correct, redirect if it is not */
    // This correction is disabled for users logged-in to the backend
    $uri_correction = $aquarius->conf('frontend/uri_correction');
    if ($uri_correction && !$direct_edit) {
        $request_url = Url::of_request();
        $canonical_url = $smarty->uri->to($node);
        $test_url = clone $request_url;
        $test_url->host = $canonical_url->host;
        $test_url->path = $canonical_url->path;
        $must_redirect = ($uri_correction === 'domain_only') ? ($test_url->host != $request_url->host) : ($test_url != $request_url);
        if ($must_redirect) {
            Log::debug("Redirecting from $request_url to $test_url");
            header('HTTP/1.1 301 Redirecting to proper URL');
            header('Expires: '.gmdate('D, d M Y H:i:s', time() + 3600)).' GMT'; // Permanent redirect? For one hour!
            header('Location: '.$test_url->str());
            exit();
        }
    }


/* Access restriction management */
    $smarty->assign('login_state', $login_state); // Save login state so that we can check for it in templates

    $user = db_Fe_users::authenticated();
    $smarty->assign('user', $user);

    // Check whether node is access restricted
    $restriction_node = $node->access_restricted_node();
    $access = true; // By default, all nodes are accessible
    if ($restriction_node) {
        Log::debug('Access restrictions for node '.$node->idstr().', by node '.$restriction_node->idstr());
        // Check with logged-in user that e has access to the restricted node
        $access = false; 
        if ($user) {
            $access = $user->hasAccessTo($restriction_node->id);
            Log::info('Access '.($access?'granted':'denied')." to user $user->id for node ".$restriction_node->idstr());
            
            // Prevent caches from storing this page
            // "private" should be enough, but because we're paranoid and spite performance we use "no-store" too.
            header('Cache-Control: no-cache, no-store, must-revalidate');
        }
    }

    if (!$access) {
        Log::debug("Require login for node ".$node->idstr());
        $restriction_node->load_content(); // Dammit
        $smarty->assign('restriction_node', $restriction_node);
        $smarty->assign('session_id', session_id());
        $template = "login"; // Divert to the login template
        
        header("Cache-Control: no-cache, must-revalidate");
    }

    Log::debug("Content template is $template.");

/* Search for the template to use */
    if (!$smarty->template_exists($template)) {
        // Try appending '.tpl' to template name
        if ($smarty->template_exists($template.'.tpl')) {
            $template = $template.'.tpl';
        } else {
            throw new AquaException(array("Missing template", "Template '$template' in form $form->title ($form->id)."));
        }
    }
    Log::debug("Smarty template '$template'");


/* Display the template */

    $module_cache_lists = $aquarius->execute_hooks('page_prepare_cache', $smarty);
    $module_cache_ids = array_flatten($module_cache_lists);
    $cache_id = '';
    $cached = false;
    if ($smarty->caching) {
        $cache_ids = array();
        $cache_ids []= $node->id;
        $cache_ids []= $lg;
        $get_params = $_GET;
        if (isset($get_params['lg'])) unset($get_params['lg']);
        if (!empty($get_params)) {
            ksort($get_params);
            $cache_ids []= md5(serialize($get_params));
        }
        $cache_id = join('.', array_merge($cache_ids, $module_cache_ids));

        if ($smarty->is_cached($template, $cache_id)) {
            Log::debug("USING SMARTY CACHE id $cache_id");
            $cached = true;
        } else {
            Log::debug("Rebuilding smarty cache for id $cache_id");
        }
    }
    if (!$cached) $aquarius->execute_hooks('page_prepare', $smarty);
    $smarty->display($template, $cache_id);


    // Let daily jobs run after the rest of the script finished
    Cron::run_on_shutdown();

} catch (Exception $error) {
    process_exception($error);
}

flush_exit();

?>