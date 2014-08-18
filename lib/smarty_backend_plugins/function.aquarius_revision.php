<?php
/** write Aquarius revision */
function smarty_function_aquarius_revision() {
    global $aquarius;
    $revision = $aquarius->revision();
    $parts = array_filter(explode('-', $revision, 2));
    $tag = array_shift($parts);
    if ($tag) return '<span title="'.htmlspecialchars($revision).'">'.htmlspecialchars($tag).'</span>';
}