<?php



/** Admin config */

// Are new nodes active by default?
defined('ADMIN_INIT_NODE_ACTIVE') or define('ADMIN_INIT_NODE_ACTIVE', true);

// Is new content active by default?
defined('ADMIN_INIT_CONTENT_ACTIVE') or define('ADMIN_INIT_CONTENT_ACTIVE', true);

// Show actions to change activation status of content
defined('ADMIN_SHOW_CONTENT_ACTIVE_FLAGS') or define('ADMIN_SHOW_CONTENT_ACTIVE_FLAGS', false);

// Frontend user Manager
defined('MAX_FE_USERS_PER_PAGE') or define('MAX_FE_USERS_PER_PAGE', 20); 

if (!defined('ECHOKEY') && isset($config['echokey'])) {
    define('ECHOKEY', $config['echokey']);
}

if (!defined('AQUARIUS_SECRET_KEY') && isset($config['secretkey'])) {
    define('AQUARIUS_SECRET_KEY', $config['secretkey']);

    // Remove secret key from config so it doesn't show up in var dumps.
    $config['secretkey'] = '******';
}

defined('DB_PASSWORD') or define('DB_PASSWORD', $config['db']['pass']);
$config['db']['pass'] = '******';

/** Filemanager config */

// root dirs for the filemanager
defined('FILE_ROOT_DIRS') or define('FILE_ROOT_DIRS', 'pictures;download');

// these directories are not listet in the filemanager
defined('BLOCKED_DIRS') or define('BLOCKED_DIRS', 'pictures/temp;pictures');

// supported picture types
defined('IMAGE_TYPES_SUPPORTED') or define('IMAGE_TYPES_SUPPORTED', 'jpg;jpeg;gif;png');

// default directory
defined('DEFAULT_SELECTED_DIR') or define('DEFAULT_SELECTED_DIR', 'pictures/content');

// maximal number of files listed per page
defined('MAX_FILES_PER_PAGE') or define('MAX_FILES_PER_PAGE', 100);

// default resizing operation: 'm' = max size   | 'w' = biggest size for width
defined('PICTURE_RESIZE') or define('PICTURE_RESIZE', 'm' );

// Resize originals on upload: '' = no resize | max size in pixels
defined('PICTURE_MAX_SIZE') or define('PICTURE_MAX_SIZE', 1000);

// default thumbnail and alt size
defined('PICTURE_TH_SIZE') or define('PICTURE_TH_SIZE', 200); 
defined('PICTURE_ALT_SIZE') or define('PICTURE_ALT_SIZE', 250);

// how much files can a user maximal upload with the filemgr
defined('FILE_MAX_UPLOAD_COUNT') or define('FILE_MAX_UPLOAD_COUNT', 15);

// list or browse files per default
defined('DEFAULT_MANAGER_STYLE') or define('DEFAULT_MANAGER_STYLE', 'list');

// Maximum width of thumbanils in filemanager
defined('FILE_SHOW_THUMB_MAX') or define('FILE_SHOW_THUMB_MAX', 200);

// Quality for JPEG
defined('QUALITY') or define('QUALITY', 95);

// File permissions for uploaded files (Attention: Keep leading zero, octal value!)
$config['filemanager']['upload_mode'] = 0777;

defined('FILEMGR_MEMORY_LIMIT') or define('FILEMGR_MEMORY_LIMIT', 128*1024*1024); // Increase memory for file operations


// Amount last changes to display
defined('LASTCHANGES_COUNT') or define('LASTCHANGES_COUNT', 50); 

defined('URL_SCHEME') or define('URL_SCHEME', isset($_SERVER['HTTPS']) ? 'https' : 'http');
defined('PROJECT_URL') or define('PROJECT_URL', URL_SCHEME.'://'.get($_SERVER, 'SERVER_NAME')."/");

/** legacy define()s
  * Some (most?) of this is not really used anymore and will be removed. */

defined('ABSOLUTE_PROJECT_URL') or define('ABSOLUTE_PROJECT_URL', PROJECT_URL);

defined('PROJECT_PATH') or define('PROJECT_PATH', $aquarius->root_path);
defined('FILEBASEDIR') or define('FILEBASEDIR', PROJECT_PATH);

defined('URL_REWRITE') or define('URL_REWRITE', true);
defined('DATE_FORMAT') or define('DATE_FORMAT', "%d.%m.%Y");


defined('PEARLOGLEVEL') or define('PEARLOGLEVEL', 0);     // use 0 in production, 5 for all debug info you'll ever need


defined('PROJECT_TITLE') or define('PROJECT_TITLE', 'aquarius');
