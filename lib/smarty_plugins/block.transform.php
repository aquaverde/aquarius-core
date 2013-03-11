<?php 
/** Pipe content of block through function
  * Param:
  *   function: Name of transform function
  *
  * Example:
  *   <code>
  *   {transform function="strip_tags"}
  *     <TAG>text</TAG>
  *   {/transform}
  *   </code> */
function smarty_block_transform($params, $content, &$smarty, &$repeat) {
    $function = get($params, 'function');
    if (!$function) throw new Exception('Missing function parameter');
    return $function($content);
}
?>