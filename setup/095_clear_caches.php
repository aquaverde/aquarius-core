<?php
/** Clear all smarty caches
 */

@unlink($aquarius->install_path.'cache/frontloader.php');

foreach(array($aquarius->get_smarty_frontend_container(db_Languages::getPrimary()), $aquarius->get_smarty_backend_container('de')) as $smarty) {
    $smarty->clear_compiled_tpl();
    $smarty->clear_cache();
}