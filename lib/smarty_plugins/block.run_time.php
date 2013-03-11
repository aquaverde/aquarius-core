<?php 
/** Output time it took to execute block */
function smarty_block_run_time($params, $content, &$smarty, &$repeat) {
    static $times = array();
    if ($repeat) array_push($times, microtime(true));
    else $content .= "<br/>Runtime: ".(microtime(true) - array_pop($times))." sec";
    return $content;
}
?>