<?php
/** write Aquarius revision */
function smarty_modifier_rev_format($revision) {
    $parts = array_filter(explode('-', $revision, 2));
    $tag = array_shift($parts);
    if ($tag) return '<span title="'.htmlspecialchars($revision).'">'.htmlspecialchars($tag).'</span>';
}