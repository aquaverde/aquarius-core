<?php
/** Override the smarty provided date_format modifier */
require_once $smarty->_get_plugin_filepath('shared','make_timestamp');

function smarty_modifier_date_format($date, $format=DATE_FORMAT, $default_date=null)
{
    if($date > 0) {
        return strftime($format, $date);
    } elseif (isset($default_date) && $default_date > 0) {
        return strftime($format, smarty_make_timestamp($default_date));
    } else {
        return "";
    }
}

?>
