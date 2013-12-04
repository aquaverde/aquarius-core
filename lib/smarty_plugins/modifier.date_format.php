<?php
/** Override the smarty provided date_format modifier */

function smarty_modifier_date_format($date, $format=DATE_FORMAT, $default_date=null) {
    $smarty->loadPlugin('smarty_shared_make_timestamp');
    if($date > 0) {
        return strftime($format, $date);
    } elseif (isset($default_date) && $default_date > 0) {
        return strftime($format, smarty_make_timestamp($default_date));
    } else {
        return "";
    }
}

