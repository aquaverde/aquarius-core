<?php
/** write Aquarius revision
  *
  * Params:
  *  plain: return string instead of prepared HTML
  */
function smarty_function_aquarius_revision($params) {
    global $aquarius;
    $revision = $aquarius->revision();
    
    if (get($params, 'plain')) return $revision;
    
    $parts = array_filter(explode('-', $revision, 2));
    $tag = array_shift($parts);
    if ($tag) return '<span title="'.htmlspecialchars($revision).'">'.htmlspecialchars($tag).'</span>';
}