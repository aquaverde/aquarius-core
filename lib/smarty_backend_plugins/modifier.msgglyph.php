<?php

function smarty_modifier_msgglyph($val) {
    switch($val) {
        case 'ok':   return 'ok';
        case 'warn': return 'warning-sign';
    }
    return $val;
}