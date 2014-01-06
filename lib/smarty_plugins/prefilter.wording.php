<?php
/* Replace occurrences of '{wording some string}' with '{w key="some string"}' */
function smarty_prefilter_wording($source, &$smarty) {
    return preg_replace('/\{wording ([^}]+)\}/', '{w key="$1"}', $source);
}
?> 