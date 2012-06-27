<?php
/** Get the name of the language for given language code */

function smarty_modifier_language_name($lg, $format="%d.%m.%Y", $default_date=null) {
    $lang = db_Languages::staticGet($lg);
    if ($lang) return $lang->name;
    else return '';
}

?>
