<?
/** Log::debug() for smarty
  */
function smarty_block_debug($params, $content, &$smarty, &$repeat) {
    if (!$repeat) Log::debug(trim($content));
    return '';
}
?>