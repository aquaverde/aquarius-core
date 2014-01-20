<?php

function smarty_modifier_msgtype($val) {
    switch($val) {
        case 'ok':   return 'success';
        case 'warn': return 'danger';
    }
    return $val;
}