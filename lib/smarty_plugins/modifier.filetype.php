<?php

/** Get the file extension in uppercase */
function smarty_modifier_filetype($string) {
    return STRTOUPPER(array_pop(explode('.', $string)));
}
