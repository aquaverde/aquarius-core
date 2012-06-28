<?php
/** Initialization operations for the backend
  * Ensures a logged-in user and initializes some globals.
  * @package Aquarius.backend
*/

Log::backtrace('backend');

/* Use sessions in backend */
    $aquarius->session_start();


/* Process logins & load logged in user */
    require_once "lib/db/Users.php";
    $logged_in = db_Users::authenticate();
                
    // Redirect to frontend if user wants to
    if ($logged_in && isset($_REQUEST['login_frontend'])) {
        header('Location:'.PROJECT_URL);
        exit;
    }

    $user = db_Users::authenticated();

    // Determine admin language
    $admin_lg = false;
    
    if ($user) {
        $admin_lg = $user->adminLanguage;
    }
    if (empty($admin_lg)) {
        $admin_lg = ADMIN_DEFAULT_LANGUAGE;
    }
    /* Determine base language to use in backend */
    function backend_lg_user_default_lang() {
        global $user;
        if ($user) return $user->defaultLanguage;
    }

    require_once "lib/Language_Detection.php";
    $language_detection = new Language_Detection;
    $language_detection->require_active = false;
    $language_detection->add_detector('request_parameter');
    $language_detection->add_detector('backend_lg_user_default_lang', 'backend_lg_user_default_lang');
    $language_detection->add_detector('accepted_languages');
    $language_detection->add_detector('primary');

    $request_params = array(
        'request' => clean_magic($_REQUEST),
        'server' => $_SERVER
    );

    $lg = $language_detection->detect($request_params);
    Log::debug("Using language ".$lg);

    $aquarius->execute_hooks('backend_init');
    
/* Create base url */

    $url = new Url(false, false);

    // Add language parameter, this one is used everywhere
    $url->add_param('lg', str($lg));


/* Divert to login if user isn't logged in */

    if (!$user) {
        $smarty = $aquarius->get_smarty_backend_container();
        $request_uri = Url::of_request();
        $correct_uri = clone $request_uri; // 'Hopefully correct' would be more to the point

        $admin_domain = $aquarius->conf('admin/domain');
        if ($admin_domain) $correct_uri->host = $admin_domain;

        // Check we're the frameset root and not an inner frame page
        if (preg_match('%/$%', $request_uri->path) === 0) {
            $config_path = $aquarius->conf('admin/path');
            if (!empty($config_path)) {
                $correct_uri->path = $config_path;
            } else {
                $correct_uri->path = dirname($correct_uri->path).'/';
            }
        }

        if ($request_uri == $correct_uri) {
            $smarty->assign('session_id', session_id());
            $smarty->display('login.tpl');
        } else {
            $smarty->assign('correct_uri', $correct_uri);
            $smarty->display('login-redirect.tpl');
        }
        flush_exit();
    }
    

/* Load libraries used in backend */
    require_once "lib/db/Users2languages.php";
    require_once "lib/adminaction.php";
    require_once "lib/moduleaction.php";
