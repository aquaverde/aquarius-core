<?php
/** @package Aquarius.frontend */

/** allow old syntax in extends and include block
  */
function smarty_prefilter_extends($source) {
    return preg_replace(
        '/.*{(extends|include) (file="|)?([^}"]*)"?}/s',
        '{$1 file="$3"}',
        $source
    );
}

