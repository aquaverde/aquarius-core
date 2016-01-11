<?php

function smarty_outputfilter_replace_aqualink($text, $smarty) {
    return replace_aqualink($text, $smarty->uri, $smarty->gettemplateVars('node'));
}
